<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../scripts/AudioImportMetadata.php';

class AudioImportMetadataTest extends TestCase
{
    private function row(array $values = []): array
    {
        return array_merge(['path' => '2016/predicaciones/ibrpm_audio_20160515M.mp3',
            'filename' => 'ibrpm_audio_20160515M.mp3', 'filenameDate' => '2016-05-15', 'filenameTurn' => 'M',
            'title' => 'Predicando el evangelio | F ilipenses 2:1-11 | 15/05/2016 M',
            'artist' => 'Andrés Birch', 'album' => 'F ilipenses 2:1-11', 'genre' => 'Predicaciones',
            'encodedBy' => '15/05/2017', 'durationSeconds' => 2632.3], $values);
    }

    public function test_date_agreement_and_reference_are_preserved(): void
    {
        $result = \PalmaAudioImportMetadata::build($this->row(), ['Filipenses']);
        $this->assertSame('2016-05-15', $result['date']);
        $this->assertSame('Predicando el evangelio', $result['title']);
        $this->assertSame('Filipenses', $result['book']);
        $this->assertSame('2:1-11', $result['citation']);
        $this->assertSame('43:52', $result['duration']);
        $this->assertTrue($result['readyToPublish']);
    }

    public function test_numbered_book_and_author_spelling_are_parsed(): void
    {
        $result = \PalmaAudioImportMetadata::build($this->row(['album' => '1ª Samuel 6:10 - 7:2',
            'artist' => 'Andrés Brich']), ['1 Samuel']);
        $this->assertSame('1 Samuel', $result['book']);
        $this->assertSame('6:10 - 7:2', $result['citation']);
        $this->assertSame('Andrés Birch', $result['author']);
    }

    public function test_missing_title_uses_factual_series_and_date(): void
    {
        $result = \PalmaAudioImportMetadata::build($this->row(['path' => 'Serie Apocalipsis/20110925M.mp3',
            'title' => null, 'album' => 'Apocalipsis', 'encodedBy' => null, 'filenameDate' => '2011-09-25']), ['Apocalipsis']);
        $this->assertSame('Apocalipsis — 25/09/2011', $result['title']);
        $this->assertSame('Apocalipsis', $result['series']);
        $this->assertNull($result['citation']);
        $this->assertTrue($result['readyToPublish']);
    }

    public function test_missing_author_is_not_invented_and_user_date_override_is_used(): void
    {
        $row = $this->row(['title' => '24/12/2024 M', 'filenameDate' => '2023-12-24',
            'encodedBy' => '24/12/2024', 'artist' => null]);
        $result = \PalmaAudioImportMetadata::build($row, [], ['date' => '2023-12-24']);
        $this->assertSame('2023-12-24', $result['date']);
        $this->assertNull($result['author']);
        $this->assertFalse($result['readyToPublish']);
        $this->assertSame(['Autor pendiente de identificar'], $result['holds']);
    }

    public function test_conflicting_dates_are_held_without_showing_a_guessed_date(): void
    {
        $row = $this->row(['filenameDate' => '2018-12-02', 'encodedBy' => null,
            'title' => 'Orar con confianza | Lucas 11:1-13 | 09/12/2018 T']);
        $result = \PalmaAudioImportMetadata::build($row, ['Lucas']);
        $this->assertNull($result['date']);
        $this->assertFalse($result['readyToPublish']);
        $this->assertStringNotContainsString('02/12/2018', $result['description']);
        $this->assertStringNotContainsString('09/12/2018', $result['description']);
        $confirmed = \PalmaAudioImportMetadata::build($row, ['Lucas'], ['date' => '2018-12-02']);
        $this->assertTrue($confirmed['readyToPublish']);
        $this->assertSame('2018-12-02', $confirmed['date']);
    }

    public function test_two_id3_date_fields_do_not_override_a_conflicting_filename_date(): void
    {
        $row = $this->row(['filenameDate' => '2023-12-24', 'title' => '24/12/2024 M',
            'encodedBy' => '24/12/2024']);
        $result = \PalmaAudioImportMetadata::build($row, ['Filipenses']);
        $this->assertNull($result['date']);
        $this->assertFalse($result['readyToPublish']);
        $this->assertSame(['Fecha discrepante pendiente de confirmar'], $result['holds']);
    }
}
