<?php
// services/GoogleSheetsService.php

namespace Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Exception;

class GoogleSheetsService {
    private $client;
    private $service;
    private $spreadsheetId;

    public function __construct($spreadsheetId = null) {
        $this->spreadsheetId = $spreadsheetId;
        
        // Inicializa o cliente do Google
        $this->client = new Client();
        $this->client->setApplicationName(APP_NAME);
        $this->client->setScopes([Sheets::SPREADSHEETS]);
        
        // Caminho para o arquivo de credenciais (JSON da Service Account)
        // Você deve colocar o seu arquivo .json na pasta /credentials/ e definir no config.php
        $credentialsPath = __DIR__ . '/../credentials/google-service-account.json';
        
        if (file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        }
        
        $this->service = new Sheets($this->client);
    }

    /**
     * Lança a frequência de uma lista de alunos para uma data específica
     */
    public function lancarFrequencia(string $abaNome, string $data, array $frequencias) {
        if (!$this->spreadsheetId) return false;

        try {
            // 1. Encontrar a coluna da data (Linha 5)
            $rangeDatas = "{$abaNome}!E5:AI5";
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $rangeDatas);
            $datasNoSheet = $response->getValues()[0] ?? [];
            
            $colunaIndex = -1;
            $dataFormatada = date('d/m/Y', strtotime($data));

            foreach ($datasNoSheet as $index => $dataSheet) {
                if (trim($dataSheet) == $dataFormatada) {
                    $colunaIndex = $index;
                    break;
                }
            }

            if ($colunaIndex === -1) {
                throw new Exception("Data {$dataFormatada} não encontrada na linha 5 da aba {$abaNome}.");
            }

            // Converter index para letra da coluna (E = 0, F = 1, ...)
            $colunaLetra = $this->indexParaColuna($colunaIndex + 4); // +4 pois começa na E (índice 4)

            // 2. Encontrar a linha de cada aluno (Coluna B, a partir da 6)
            $rangeAlunos = "{$abaNome}!B6:B60"; // Ajuste o limite conforme necessário
            $responseAlunos = $this->service->spreadsheets_values->get($this->spreadsheetId, $rangeAlunos);
            $nomesNoSheet = $responseAlunos->getValues() ?? [];

            $batchUpdates = [];

            foreach ($frequencias as $alunoNome => $status) {
                $linhaIndex = -1;
                foreach ($nomesNoSheet as $idx => $row) {
                    $nomeSheet = $row[0] ?? '';
                    if (trim($nomeSheet) == trim($alunoNome)) {
                        $linhaIndex = $idx + 6; // +6 pois começa na linha 6
                        break;
                    }
                }

                if ($linhaIndex !== -1) {
                    $valor = ($status === 'PRESENTE') ? '.' : 'f';
                    $celula = "{$abaNome}!{$colunaLetra}{$linhaIndex}";
                    
                    $batchUpdates[] = new Sheets\ValueRange([
                        'range' => $celula,
                        'values' => [[$valor]]
                    ]);
                }
            }

            if (!empty($batchUpdates)) {
                $body = new Sheets\BatchUpdateValuesRequest([
                    'valueInputOption' => 'RAW',
                    'data' => $batchUpdates
                ]);
                $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $body);
            }

            return true;

        } catch (Exception $e) {
            error_log("Erro no Google Sheets Service: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Auxiliar: Converte índice numérico para letra de coluna (0=A, 1=B...)
     */
    private function indexParaColuna($index) {
        $letra = '';
        while ($index >= 0) {
            $letra = chr(($index % 26) + 65) . $letra;
            $index = floor($index / 26) - 1;
        }
        return $letra;
    }
}
