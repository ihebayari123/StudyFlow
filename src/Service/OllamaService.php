<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaService
{
    private string $ollamaUrl;
    private string $model;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $ollamaUrl = 'http://localhost:11434',
        string $model = 'mistral'
    ) {
        $this->ollamaUrl = $ollamaUrl;
        $this->model     = $model;
    }

    public function generateResponse(string $prompt): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->ollamaUrl . '/api/generate', [
                'json' => [
                    'model'   => $this->model,
                    'prompt'  => $prompt,
                    'stream'  => false,
                    'temperature' => 0.1,
                    'num_predict' => 500,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            return $data['response'] ?? '';

        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Cannot reach Ollama. Make sure it is running: `ollama serve`. Error: ' . $e->getMessage()
            );
        }
    }

    public function generateCourse(string $courseName, int $chapterCount = 5): array
    {
        $prompt = $this->buildPrompt($courseName, $chapterCount);

        try {
            $response = $this->httpClient->request('POST', $this->ollamaUrl . '/api/generate', [
                'json' => [
                    'model'   => $this->model,
                    'prompt'  => $prompt,
                    'stream'  => false,
                    'format'  => 'json',
                    'options' => [
                        'temperature' => 0.7,
                        'num_predict' => 8000,
                        'num_ctx'     => 8000,
                    ],
                ],
                'timeout' => 180,
            ]);

            $data    = $response->toArray();
            $rawJson = $data['response'] ?? '';

            return $this->parseAndValidate($rawJson, $courseName, $chapterCount);

        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new \RuntimeException(
                'Cannot reach Ollama. Make sure it is running: `ollama serve`. Error: ' . $e->getMessage()
            );
        }
    }

    // -------------------------------------------------------------------------
    // Prompt — ask ONLY for text content, no URLs (AI invents fake ones)
    // -------------------------------------------------------------------------
    private function buildPrompt(string $courseName, int $chapterCount): string
    {
        return <<<PROMPT
You are a course creator. Generate a course about "{$courseName}".

Return ONLY this JSON with no extra text:
{
  "titre": "Course title",
  "description": "Short description under 150 characters",
  "chapitres": [
    {
      "ordre": 1,
      "titre": "Chapter title",
      "contenu": "Two or three paragraphs explaining this chapter."
    }
  ]
}

Rules:
- Exactly {$chapterCount} chapters
- description: under 150 characters
- contenu: minimum 2 paragraphs
- Return ONLY the JSON, nothing else
PROMPT;
    }

    // -------------------------------------------------------------------------
    // Parse AI response — generate video/links in PHP (never trust AI for URLs)
    // -------------------------------------------------------------------------
    private function parseAndValidate(string $rawJson, string $fallbackName, int $expectedChapters): array
    {
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($rawJson));
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            // Try to repair truncated JSON
            $cleaned = $this->repairJson($cleaned);
            $decoded = json_decode($cleaned, true);
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new \RuntimeException(
                'Ollama returned invalid JSON. Raw: ' . substr($rawJson, 0, 300)
            );
        }

        $course = [
            'titre'       => $decoded['titre'] ?? $fallbackName,
            'description' => mb_substr($decoded['description'] ?? 'A course about ' . $fallbackName, 0, 255),
            'chapitres'   => [],
        ];

        $rawChapters = $decoded['chapitres'] ?? $decoded['chapters'] ?? [];

        foreach ($rawChapters as $index => $ch) {
            $chapterTitle = $ch['titre'] ?? $ch['title'] ?? 'Chapter ' . ($index + 1);

            // ✅ Generate video URL in PHP — guaranteed to work
            $searchQuery = urlencode($fallbackName . ' ' . $chapterTitle . ' tutorial');
            $videoUrl    = 'https://www.youtube.com/results?search_query=' . $searchQuery;

            // ✅ Generate links in PHP — guaranteed to work
            $links = $this->generateLinks($fallbackName, $chapterTitle, $index);

            $course['chapitres'][] = [
                'titre'           => $chapterTitle,
                'contenu'         => $ch['contenu'] ?? $ch['content'] ?? 'Content coming soon.',
                'ordre'           => (int)($ch['ordre'] ?? $ch['order'] ?? $index + 1),
                'durationMinutes' => (int)($ch['durationMinutes'] ?? 30),
                'contentType'     => 'mixed',
                'videoUrl'        => $videoUrl,   // ✅ real working URL
                'links'           => $links,       // ✅ real working links
            ];
        }

        usort($course['chapitres'], fn($a, $b) => $a['ordre'] <=> $b['ordre']);

        return $course;
    }

    // -------------------------------------------------------------------------
    // Repair truncated JSON
    // -------------------------------------------------------------------------
    private function repairJson(string $json): string
    {
        $json = preg_replace('/,\s*\{[^}]*$/', '', $json);
        $json = rtrim($json, ", \t\n\r");

        if (substr_count($json, '[') > substr_count($json, ']')) {
            $json .= ']';
        }
        if (substr_count($json, '{') > substr_count($json, '}')) {
            $json .= '}';
        }

        return $json;
    }

    // -------------------------------------------------------------------------
    // Generate real working links based on course topic
    // -------------------------------------------------------------------------
    private function generateLinks(string $courseName, string $chapterTitle, int $index): array
    {
        $searchQuery = urlencode($courseName . ' ' . $chapterTitle);

        $topicLinks = [
            'python'           => [
                ['title' => 'Python Official Docs',  'url' => 'https://docs.python.org/3/'],
                ['title' => 'Real Python Tutorials', 'url' => 'https://realpython.com/'],
                ['title' => 'W3Schools Python',      'url' => 'https://www.w3schools.com/python/'],
            ],
            'javascript'       => [
                ['title' => 'MDN Web Docs',           'url' => 'https://developer.mozilla.org/en-US/docs/Web/JavaScript'],
                ['title' => 'JavaScript.info',        'url' => 'https://javascript.info/'],
                ['title' => 'W3Schools JS',           'url' => 'https://www.w3schools.com/js/'],
            ],
            'php'              => [
                ['title' => 'PHP Official Docs',     'url' => 'https://www.php.net/docs.php'],
                ['title' => 'PHP The Right Way',     'url' => 'https://phptherightway.com/'],
                ['title' => 'W3Schools PHP',         'url' => 'https://www.w3schools.com/php/'],
            ],
            'java'             => [
                ['title' => 'Java Official Docs',    'url' => 'https://docs.oracle.com/en/java/'],
                ['title' => 'Baeldung Tutorials',    'url' => 'https://www.baeldung.com/'],
                ['title' => 'W3Schools Java',        'url' => 'https://www.w3schools.com/java/'],
            ],
            'react'            => [
                ['title' => 'React Official Docs',   'url' => 'https://react.dev/'],
                ['title' => 'React Tutorial',        'url' => 'https://react.dev/learn'],
                ['title' => 'MDN Web Docs',          'url' => 'https://developer.mozilla.org/'],
            ],
            'sql'              => [
                ['title' => 'W3Schools SQL',         'url' => 'https://www.w3schools.com/sql/'],
                ['title' => 'SQLZoo Practice',       'url' => 'https://sqlzoo.net/'],
                ['title' => 'MySQL Docs',            'url' => 'https://dev.mysql.com/doc/'],
            ],
            'machine learning' => [
                ['title' => 'Scikit-learn Docs',     'url' => 'https://scikit-learn.org/stable/'],
                ['title' => 'Kaggle Learn',          'url' => 'https://www.kaggle.com/learn'],
                ['title' => 'fast.ai',               'url' => 'https://www.fast.ai/'],
            ],
            'marketing'        => [
                ['title' => 'HubSpot Academy',       'url' => 'https://academy.hubspot.com/'],
                ['title' => 'Google Digital Garage', 'url' => 'https://learndigital.withgoogle.com/'],
                ['title' => 'Moz SEO Guide',         'url' => 'https://moz.com/beginners-guide-to-seo'],
            ],
            'design'           => [
                ['title' => 'Figma Tutorials',       'url' => 'https://www.figma.com/resources/learn-design/'],
                ['title' => 'Canva Design School',   'url' => 'https://www.canva.com/learn/'],
                ['title' => 'Adobe Tutorials',       'url' => 'https://helpx.adobe.com/creative-cloud/tutorials-explore.html'],
            ],
            'css'              => [
                ['title' => 'MDN CSS Docs',          'url' => 'https://developer.mozilla.org/en-US/docs/Web/CSS'],
                ['title' => 'CSS Tricks',            'url' => 'https://css-tricks.com/'],
                ['title' => 'W3Schools CSS',         'url' => 'https://www.w3schools.com/css/'],
            ],
        ];

        $courseNameLower = strtolower($courseName);
        foreach ($topicLinks as $keyword => $links) {
            if (str_contains($courseNameLower, $keyword)) {
                $offset = $index % count($links);
                return [
                    $links[$offset],
                    $links[($offset + 1) % count($links)],
                ];
            }
        }

        // Generic fallback
        return [
            ['title' => 'Google Search',  'url' => 'https://www.google.com/search?q=' . $searchQuery],
            ['title' => 'Wikipedia',      'url' => 'https://en.wikipedia.org/wiki/Special:Search?search=' . urlencode($courseName)],
        ];
    }
}