"""Transcribe local audio content for metadata; no speaker recognition or comparison."""

import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import sys

parser = argparse.ArgumentParser()
parser.add_argument("inventory", type=Path)
parser.add_argument("output_dir", type=Path)
parser.add_argument("--runtime-dir", required=True, type=Path)
parser.add_argument("--model-dir", required=True, type=Path)
parser.add_argument("--source-path", action="append")
parser.add_argument("--seconds", type=int, default=240)
parser.add_argument("--batch-size", type=int, default=1)
args = parser.parse_args()
sys.path.insert(0, str(args.runtime_dir.resolve()))
os.environ["HF_HUB_DISABLE_IMPLICIT_TOKEN"] = "1"
os.environ["HF_HOME"] = str(args.model_dir.resolve().parent / "hf-cache")
from huggingface_hub import snapshot_download
from faster_whisper import WhisperModel, BatchedInferencePipeline

inventory = json.loads(args.inventory.read_text(encoding="utf-8-sig"))
source_root = Path(inventory["source"]).resolve()
args.output_dir.mkdir(parents=True, exist_ok=True)
args.model_dir.mkdir(parents=True, exist_ok=True)
if not (args.model_dir / "model.bin").exists():
    print("Downloading public speech-to-text model", flush=True)
    snapshot_download("Systran/faster-whisper-small", token=False,
                      local_dir=str(args.model_dir),
                      allow_patterns=["config.json", "model.bin", "tokenizer.json", "vocabulary.*", "preprocessor_config.json"])
print("Loading CPU speech-to-text model", flush=True)
model = WhisperModel(str(args.model_dir), device="cpu", compute_type="int8", cpu_threads=4)
pipeline = BatchedInferencePipeline(model) if args.batch_size > 1 else None
selected = set(args.source_path or [])
for row in inventory["rows"]:
    title = (row.get("title") or "").split("|")[0].strip()
    needs_title = not title or re.fullmatch(r"\d{1,2}/\d{1,2}/\d{4}\s*[MTE]?", title)
    if (selected and row["path"] not in selected) or (not selected and not needs_title):
        continue
    source = (source_root / row["path"]).resolve()
    if not source.is_relative_to(source_root):
        raise ValueError("Audio path leaves the source directory")
    destination = args.output_dir / (source.name + ".json")
    if destination.exists():
        previous = json.loads(destination.read_text(encoding="utf-8"))
        if previous.get("inputSha256") == row.get("sha256") and previous.get("clipSeconds", 0) >= args.seconds:
            print("Already transcribed: " + row["path"], flush=True)
            continue
    print("Transcribing: " + row["path"], flush=True)
    if pipeline:
        clips = [{"start": s, "end": min(s + 30, args.seconds)} for s in range(0, args.seconds, 30)]
        segments, info = pipeline.transcribe(str(source), language="es", beam_size=3, temperature=0,
                                             clip_timestamps=clips, batch_size=args.batch_size)
    else:
        segments, info = model.transcribe(str(source), language="es", beam_size=3, temperature=0,
                                          clip_timestamps=f"0,{args.seconds}",
                                          condition_on_previous_text=False, vad_filter=True)
    result = []
    for segment in segments:
        result.append({"start": segment.start, "end": segment.end, "text": segment.text.strip(),
                       "avgLogProbability": segment.avg_logprob, "noSpeechProbability": segment.no_speech_prob})
    report = {"sourcePath": row["path"], "inputSha256": row.get("sha256"),
              "model": "Systran/faster-whisper-small", "language": info.language,
              "clipSeconds": args.seconds, "speakerIdentification": False, "segments": result}
    destination.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    destination.with_suffix(".txt").write_text("\n".join(f"[{s['start']:.1f}] {s['text']}" for s in result), encoding="utf-8")
    print(f"Saved {len(result)} segments: {destination.name}", flush=True)
