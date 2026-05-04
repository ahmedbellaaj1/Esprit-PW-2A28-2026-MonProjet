<?php

declare(strict_types=1);

session_start();

// Vérifier si le code CAPTCHA existe en session
if (!isset($_SESSION['captcha_code'])) {
    http_response_code(400);
    exit('CAPTCHA not generated');
}

header('Content-Type: audio/wav');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Générer un fichier WAV simple avec les sons des caractères
generateCaptchaAudio($_SESSION['captcha_code']);

function generateCaptchaAudio(string $code): void
{
    $sampleRate = 22050;
    $baseDuration = 0.4; // Durée de chaque son
    $silenceDuration = 0.2; // Silence entre les sons
    
    $samples = [];
    
    // Ajouter un silence au début
    for ($i = 0; $i < (int)($sampleRate * 0.3); $i++) {
        $samples[] = 0;
    }
    
    // Générer un son pour chaque caractère
    foreach (str_split($code) as $index => $char) {
        // Convertir le caractère en fréquence (A=440Hz, Z=1000Hz, 0=800Hz, 9=900Hz)
        $charValue = ord($char);
        
        if ($charValue >= ord('A') && $charValue <= ord('Z')) {
            // Majuscules: 400-800 Hz
            $frequency = 400 + (($charValue - ord('A')) / 26) * 400;
        } elseif ($charValue >= ord('a') && $charValue <= ord('z')) {
            // Minuscules: 800-1200 Hz
            $frequency = 800 + (($charValue - ord('a')) / 26) * 400;
        } else {
            // Chiffres: 1200-1600 Hz
            $frequency = 1200 + (($charValue - ord('0')) / 10) * 400;
        }
        
        // Générer le son pour ce caractère
        $numSamples = (int)($sampleRate * $baseDuration);
        
        for ($i = 0; $i < $numSamples; $i++) {
            $t = $i / $sampleRate;
            
            // Générer une onde sinusoïdale
            $sample = (int)(32767 * 0.4 * sin(2 * M_PI * $frequency * $t));
            
            // Appliquer une enveloppe ADSR simple
            $envelope = 1;
            
            // Attack (100ms)
            if ($i < 2205) {
                $envelope = $i / 2205;
            }
            // Release (100ms)
            elseif ($i > $numSamples - 2205) {
                $envelope = ($numSamples - $i) / 2205;
            }
            
            $samples[] = (int)($sample * $envelope);
        }
        
        // Ajouter un silence entre les caractères
        $silenceSamples = (int)($sampleRate * $silenceDuration);
        for ($i = 0; $i < $silenceSamples; $i++) {
            $samples[] = 0;
        }
        
        // Ajouter un "bip" pour marquer la séparation
        $bipSamples = (int)($sampleRate * 0.1);
        for ($i = 0; $i < $bipSamples; $i++) {
            $t = $i / $sampleRate;
            $bipSample = (int)(32767 * 0.2 * sin(2 * M_PI * 800 * $t));
            
            $envelope = 1;
            if ($i < 220) {
                $envelope = $i / 220;
            } elseif ($i > $bipSamples - 220) {
                $envelope = ($bipSamples - $i) / 220;
            }
            
            $samples[] = (int)($bipSample * $envelope);
        }
    }
    
    // Convertir les samples en format WAV et envoyer
    sendWavFile($samples, $sampleRate);
}

function sendWavFile(array $samples, int $sampleRate): void
{
    $numSamples = count($samples);
    $numChannels = 1;
    $bitsPerSample = 16;
    $byteRate = $sampleRate * $numChannels * $bitsPerSample / 8;
    $blockAlign = $numChannels * $bitsPerSample / 8;
    $subchunk2Size = $numSamples * $numChannels * $bitsPerSample / 8;
    $chunkSize = 36 + $subchunk2Size;
    
    // Entête WAV
    echo 'RIFF';
    echo pack('V', $chunkSize);
    echo 'WAVE';
    echo 'fmt ';
    echo pack('V', 16); // Sous-chunk1 size
    echo pack('v', 1);  // Format audio (1 = PCM)
    echo pack('v', $numChannels);
    echo pack('V', $sampleRate);
    echo pack('V', $byteRate);
    echo pack('v', $blockAlign);
    echo pack('v', $bitsPerSample);
    
    // Sous-chunk2 (données audio)
    echo 'data';
    echo pack('V', $subchunk2Size);
    
    // Données d'audio
    foreach ($samples as $sample) {
        $clipped = max(-32768, min(32767, (int)$sample));
        echo pack('v', $clipped);
    }
}

