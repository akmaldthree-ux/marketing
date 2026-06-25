<?php
namespace App\Support;

/**
 * Minimal XLSX writer using ZipArchive + XML — no external dependencies.
 * Supports strings, numbers, and basic styling (bold header row + comment rows in gray).
 */
class XlsxWriter
{
    /**
     * Build an XLSX binary string from a 2D array of rows.
     * Rows whose first cell starts with '#' are rendered as gray comment rows.
     * The first non-comment row is rendered as a bold header row.
     */
    public static function build(array $rows): string
    {
        $sharedStrings = [];
        $ssIndex = [];

        $getStringIdx = function (string $s) use (&$sharedStrings, &$ssIndex): int {
            if (!isset($ssIndex[$s])) {
                $ssIndex[$s] = count($sharedStrings);
                $sharedStrings[] = $s;
            }
            return $ssIndex[$s];
        };

        // Style indices:
        // 0 = normal, 1 = bold (header), 2 = gray comment
        $cellXml = '';
        $rowNum  = 0;
        $headerWritten = false;

        foreach ($rows as $row) {
            $rowNum++;
            $row = array_values((array)$row);
            if (empty($row)) { $cellXml .= "<row r=\"{$rowNum}\"/>"; continue; }

            $firstCell = (string)($row[0] ?? '');
            $isComment = str_starts_with($firstCell, '#');
            $isHeader  = !$isComment && !$headerWritten;
            if ($isHeader) $headerWritten = true;

            $styleIdx = $isComment ? 2 : ($isHeader ? 1 : 0);

            $cellXml .= "<row r=\"{$rowNum}\">";
            foreach ($row as $colIdx => $val) {
                $colLetter = self::colLetter($colIdx);
                $cellRef   = $colLetter . $rowNum;
                $val       = (string)($val ?? '');

                if ($val !== '' && is_numeric($val) && !$isComment) {
                    $cellXml .= "<c r=\"{$cellRef}\" s=\"{$styleIdx}\"><v>" . htmlspecialchars($val, ENT_XML1) . "</v></c>";
                } else {
                    $idx      = $getStringIdx($val);
                    $cellXml .= "<c r=\"{$cellRef}\" t=\"s\" s=\"{$styleIdx}\"><v>{$idx}</v></c>";
                }
            }
            $cellXml .= "</row>";
        }

        // --- Build XML parts ---
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $cellXml . '</sheetData></worksheet>';

        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $s) {
            $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1) . '</t></si>';
        }
        $ssXml .= '</sst>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'                          // 0 normal
            .   '<font><b/><sz val="11"/><name val="Calibri"/></font>'                      // 1 bold
            .   '<font><sz val="11"/><name val="Calibri"/><color rgb="FF888888"/></font>'   // 2 gray
            . '</fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'   // 0 normal
            .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'   // 1 bold
            .   '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/>'   // 2 gray
            . '</cellXfs>'
            . '</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        // --- Pack into ZIP ---
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml',            $contentTypes);
        $zip->addFromString('_rels/.rels',                    $rootRels);
        $zip->addFromString('xl/workbook.xml',                $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels',     $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml',       $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml',           $ssXml);
        $zip->addFromString('xl/styles.xml',                  $stylesXml);
        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $content;
    }

    /** Convert 0-based column index to Excel column letter (A, B, ..., Z, AA, ...) */
    private static function colLetter(int $idx): string
    {
        $letter = '';
        $idx++;
        while ($idx > 0) {
            $idx--;
            $letter = chr(65 + ($idx % 26)) . $letter;
            $idx    = intdiv($idx, 26);
        }
        return $letter;
    }

    /** Return an HTTP response that triggers XLSX download */
    public static function download(string $filename, array $rows): \Illuminate\Http\Response
    {
        $content = self::build($rows);
        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($content),
        ]);
    }
}
