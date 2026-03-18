<?php

namespace App\Services\PatientSchedule;

use App\Models\Patient;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

class PatientScheduleDocxGenerator
{
    /**
     * Génère un fichier DOCX (100% local) et retourne le chemin du fichier temporaire.
     */
    public function generateToTempFile(Patient $patient, PatientScheduleData $data, ?string $diagnosticSummary): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(2.0),
            'marginBottom' => Converter::cmToTwip(2.0),
            'marginLeft' => Converter::cmToTwip(1.8),
            'marginRight' => Converter::cmToTwip(1.8),
        ]);

        $blue = '004a99';
        $gray = '6b7280';
        $light = 'f4f4f4';

        // Header (logo + cabinet info)
        $cabinet = config('cabinet');
        $logoPath = $cabinet['logo_path'] ?? null;
        if ($logoPath && is_string($logoPath) && $logoPath !== '' && file_exists($logoPath)) {
            $section->addImage($logoPath, ['height' => 42, 'wrappingStyle' => 'inline']);
        }

        $section->addText(strtoupper($cabinet['name'] ?? 'Healthcare Balance'), [
            'bold' => true,
            'size' => 16,
            'color' => $blue,
        ], ['alignment' => 'center']);

        $section->addText(($cabinet['tagline'] ?? ''), [
            'bold' => true,
            'size' => 11,
            'color' => '374151',
        ], ['alignment' => 'center']);

        $contact = trim(($cabinet['phone'] ?? '') . ' | ' . ($cabinet['email'] ?? ''));
        $address = (string) ($cabinet['address'] ?? '');
        $section->addText($contact, ['size' => 9, 'color' => $gray], ['alignment' => 'center']);
        if ($address !== '') {
            $section->addText($address, ['size' => 9, 'color' => $gray], ['alignment' => 'center']);
        }

        $section->addTextBreak(1);

        // Title
        $section->addText('CALENDRIER DES SÉANCES PATIENT', [
            'bold' => true,
            'size' => 14,
            'color' => $blue,
        ], ['alignment' => 'center']);
        $section->addText('Document simplifié remis au patient', [
            'size' => 9,
            'color' => $gray,
        ], ['alignment' => 'center']);

        $section->addTextBreak(1);

        // Patient info + diagnostic
        $section->addText('Nom du patient : ' . $patient->fullName(), ['size' => 11, 'color' => '111111']);
        $section->addText('Diagnostic : ' . ($diagnosticSummary ?? ''), ['size' => 11, 'color' => '111111']);

        $periodText = '';
        if ($data->periodStart && $data->periodEnd) {
            $periodText = $data->periodStart->format('d/m/Y') . ' — ' . $data->periodEnd->format('d/m/Y');
            $days = $data->totalTreatmentDays();
            if ($days) {
                $periodText .= ' (' . $days . ' jour' . ($days > 1 ? 's' : '') . ' de traitement)';
            }
        }
        $section->addText('Période : ' . $periodText, ['size' => 11, 'color' => '111111']);
        $section->addText('Nombre de séances : ' . ($data->totalSessions ?: ''), ['size' => 11, 'color' => '111111']);

        $section->addTextBreak(1);
        $section->addText('Séances prévues — Les colonnes ci-dessous affichent uniquement la date, l’heure et le pôle.', [
            'size' => 9,
            'color' => $gray,
        ]);

        // Table style
        $phpWord->addTableStyle('ScheduleTable', [
            'borderSize' => 6,
            'borderColor' => 'd1d5db',
            'cellMargin' => 80,
        ], [
            'bgColor' => $light,
        ]);

        $table = $section->addTable('ScheduleTable');
        $table->addRow();
        $table->addCell(Converter::cmToTwip(1.2))->addText('N°', ['bold' => true, 'size' => 9, 'color' => $blue]);
        $table->addCell(Converter::cmToTwip(3.0))->addText('Date', ['bold' => true, 'size' => 9, 'color' => $blue]);
        $table->addCell(Converter::cmToTwip(3.0))->addText('Heure', ['bold' => true, 'size' => 9, 'color' => $blue]);
        $table->addCell(Converter::cmToTwip(7.0))->addText('Pôle', ['bold' => true, 'size' => 9, 'color' => $blue]);

        $rows = $data->items->values();
        $maxRows = max($rows->count(), 12);
        for ($i = 0; $i < $maxRows; $i++) {
            $item = $rows->get($i);
            $table->addRow();
            $table->addCell(Converter::cmToTwip(1.2))->addText((string) ($i + 1));
            $table->addCell(Converter::cmToTwip(3.0))->addText($item?->date?->format('d/m/Y') ?? '');
            $table->addCell(Converter::cmToTwip(3.0))->addText((string) ($item?->time ?? ''));
            $table->addCell(Converter::cmToTwip(7.0))->addText((string) ($item?->pole ?? ''));
        }

        $section->addTextBreak(1);
        $section->addText(
            'Note : ce calendrier est fourni au patient comme document de suivi thérapeutique. Il peut être mis à jour sans modifier le calendrier interne du cabinet.',
            ['size' => 8.5, 'color' => $gray]
        );

        $section->addTextBreak(2);
        $section->addText('Cachet et signature du cabinet', ['size' => 10, 'color' => '111111'], ['alignment' => 'right']);
        $section->addText('__________________________', ['size' => 10, 'color' => '111111'], ['alignment' => 'right']);

        $section->addTextBreak(1);
        $section->addText('Healthcare Balance - document patient', ['size' => 8.5, 'color' => $gray], ['alignment' => 'right']);

        $tmp = tempnam(sys_get_temp_dir(), 'hb_schedule_');
        if (! $tmp) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }
        $path = $tmp . '.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }
}

