"""Compare local getID3 inventory with the catalogue exported from the admin UI.

This compares metadata, not binary identity. Originals without a catalogue match
are candidates for upload; files on the server without records need SSH to audit.
"""

import argparse
import csv
import json
import re
import unicodedata
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path


def normalize(value):
    value = unicodedata.normalize("NFKD", (value or "").split("|")[0].casefold())
    return re.sub(r"[^a-z0-9]+", "", value)


def seconds(value):
    try:
        return sum(int(part) * 60**i for i, part in enumerate(reversed(value.split(":"))))
    except (ValueError, AttributeError):
        return None


def catalogue_date(value):
    try:
        return datetime.strptime(value, "%d/%m/%Y").strftime("%Y-%m-%d")
    except (ValueError, TypeError):
        return None


def compare(originals, catalogue):
    if len({row["path"] for row in originals}) != len(originals):
        raise ValueError("Original paths must be unique")
    if len({row["id"] for row in catalogue}) != len(catalogue):
        raise ValueError("Catalogue IDs must be unique")
    unmatched_local = set(range(len(originals)))
    unmatched_remote = set(range(len(catalogue)))
    matches = []

    def duration_matches(local, remote):
        left, right = local.get("durationSeconds"), seconds(remote.get("duration"))
        return left is not None and right is not None and abs(left - right) <= 2

    def title_matches(local, remote):
        title = normalize(local.get("title"))
        return bool(title) and title == normalize(remote.get("title"))

    def date_matches(local, remote):
        date = catalogue_date(remote.get("date"))
        return bool(date) and date == local.get("filenameDate")

    rules = [
        ("titulo_fecha_duracion", lambda a, b: title_matches(a, b) and date_matches(a, b) and duration_matches(a, b)),
        ("titulo_duracion", lambda a, b: title_matches(a, b) and duration_matches(a, b)),
        ("fecha_duracion", lambda a, b: date_matches(a, b) and duration_matches(a, b)),
    ]
    for label, predicate in rules:
        candidates = {i: [j for j in sorted(unmatched_remote) if predicate(originals[i], catalogue[j])]
                      for i in sorted(unmatched_local)}
        reverse = Counter(j for targets in candidates.values() for j in targets)
        for i, targets in candidates.items():
            if len(targets) == 1 and reverse[targets[0]] == 1:
                j = targets[0]
                unmatched_local.remove(i)
                unmatched_remote.remove(j)
                matches.append({"path": originals[i]["path"], "id": catalogue[j]["id"], "method": label,
                                "status": catalogue[j]["status"], "date": catalogue[j]["date"], "title": catalogue[j]["title"]})

    missing = [originals[i] for i in sorted(unmatched_local)]
    extra = [catalogue[j] for j in sorted(unmatched_remote)]
    return {
        "consultedAt": datetime.now(timezone.utc).isoformat(),
        "limitations": "Cruce por metadatos, no por hash. Sin SSH no se detectan archivos del servidor sin registro ni se acredita identidad binaria.",
        "summary": {
            "original_files": len(originals), "catalogue_records": len(catalogue),
            "catalogue_status": dict(Counter(row["status"] for row in catalogue)),
            "matched_originals": len(matches), "unmatched_originals": len(missing),
            "unmatched_catalogue": len(extra), "match_methods": dict(Counter(row["method"] for row in matches)),
            "unmatched_originals_by_folder": dict(sorted(Counter(row["path"].split("/")[0] for row in missing).items())),
        },
        "matches": matches, "originals_without_catalogue_match": missing,
        "catalogue_without_original_match": extra,
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("originals", type=Path)
    parser.add_argument("catalogue", type=Path)
    parser.add_argument("output", type=Path)
    parser.add_argument("--file-headers", type=Path, help="Optional output of check-audio-files.php")
    args = parser.parse_args()
    originals = json.loads(args.originals.read_text(encoding="utf-8"))["rows"]
    catalogue = json.loads(args.catalogue.read_text(encoding="utf-8"))["rows"]
    result = compare(originals, catalogue)
    if args.file_headers:
        headers = {row["id"]: row for row in json.loads(args.file_headers.read_text(encoding="utf-8"))["rows"]}
        by_path = {row["path"]: row for row in originals}
        for match in result["matches"]:
            file = headers.get(match["id"], {})
            match["http_status"] = file.get("status")
            match["same_size"] = file.get("status") == 200 and file.get("size") == by_path[match["path"]]["size"]
        result["summary"]["file_http_statuses"] = dict(Counter(row["status"] for row in headers.values()))
        result["summary"]["matches_with_same_size"] = sum(row["same_size"] for row in result["matches"])
        result["summary"]["files_with_transient_errors"] = [row["id"] for row in headers.values() if row.get("previousStatus")]
    args.output.mkdir(parents=True, exist_ok=True)
    (args.output / "comparacion-inventario.json").write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
    fields = ["path", "filenameDate", "filenameTurn", "title", "artist", "durationSeconds", "size"]
    with (args.output / "originales-sin-coincidencia.csv").open("w", newline="", encoding="utf-8-sig") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields, extrasaction="ignore")
        writer.writeheader()
        writer.writerows(result["originals_without_catalogue_match"])
    print(json.dumps(result["summary"], ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
