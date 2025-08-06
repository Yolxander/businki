<?php

namespace App\Services;

class IntentDetectionService
{
    /**
     * Detect client-related intents from user message
     */
    public function detectClientIntent(string $message, array $previousContext = []): array
    {
        $message = strtolower(trim($message));

        $intent = [
            'type' => 'none',
            'action' => null,
            'data' => [],
            'confidence' => 0
        ];

        // Create client intent
        if ($this->isCreateIntent($message)) {
            $intent = [
                'type' => 'client',
                'action' => 'create',
                'data' => $this->extractClientData($message),
                'confidence' => $this->calculateConfidence($message, 'create')
            ];
        }
        // Read client intent
        elseif ($this->isReadIntent($message)) {
            $intent = [
                'type' => 'client',
                'action' => 'read',
                'data' => $this->extractSearchCriteria($message),
                'confidence' => $this->calculateConfidence($message, 'read')
            ];
        }
        // Update client intent
        elseif ($this->isUpdateIntent($message)) {
            $intent = [
                'type' => 'client',
                'action' => 'update',
                'data' => $this->extractUpdateData($message),
                'confidence' => $this->calculateConfidence($message, 'update')
            ];
        }
        // Delete client intent
        elseif ($this->isDeleteIntent($message)) {
            $intent = [
                'type' => 'client',
                'action' => 'delete',
                'data' => $this->extractSearchCriteria($message),
                'confidence' => $this->calculateConfidence($message, 'delete')
            ];
        }
        // List clients intent
        elseif ($this->isListIntent($message)) {
            $intent = [
                'type' => 'client',
                'action' => 'list',
                'data' => $this->extractListFilters($message),
                'confidence' => $this->calculateConfidence($message, 'list')
            ];
        }
        // Check if this is a follow-up message providing missing information
        elseif (!empty($previousContext) && $this->isProvidingAdditionalInfo($message, $previousContext)) {
            $intent = [
                'type' => 'client',
                'action' => 'create',
                'data' => $this->extractAdditionalClientData($message, $previousContext),
                'confidence' => 0.9,
                'is_followup' => true
            ];
        }

        return $intent;
    }

    /**
     * Check if message is a create intent
     */
    private function isCreateIntent(string $message): bool
    {
        $createPatterns = [
            'create client',
            'add client',
            'new client',
            'register client',
            'add a client',
            'create a client',
            'new customer',
            'add customer'
        ];

        foreach ($createPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is a read intent
     */
    private function isReadIntent(string $message): bool
    {
        $readPatterns = [
            'show client',
            'find client',
            'get client',
            'view client',
            'search client',
            'look up client',
            'client details',
            'client info',
            'who is',
            'tell me about'
        ];

        foreach ($readPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is an update intent
     */
    private function isUpdateIntent(string $message): bool
    {
        $updatePatterns = [
            'update client',
            'edit client',
            'modify client',
            'change client',
            'update client info',
            'edit client details',
            'modify client information'
        ];

        foreach ($updatePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is a delete intent
     */
    private function isDeleteIntent(string $message): bool
    {
        $deletePatterns = [
            'delete client',
            'remove client',
            'delete customer',
            'remove customer',
            'delete client record',
            'remove client record'
        ];

        foreach ($deletePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is a list intent
     */
    private function isListIntent(string $message): bool
    {
        $listPatterns = [
            'list clients',
            'show all clients',
            'all clients',
            'client list',
            'show clients',
            'get all clients',
            'list all clients',
            'client directory'
        ];

        foreach ($listPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract client data from create message
     */
    private function extractClientData(string $message): array
    {
        $data = [];

        // Extract name
        $namePatterns = [
            'name is ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'named ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'called ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'first name ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'last name ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)'
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $message, $matches)) {
                $name = trim($matches[1]);
                $nameParts = explode(' ', $name);

                if (count($nameParts) >= 2) {
                    $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                    $data['last_name'] = ucfirst(strtolower(implode(' ', array_slice($nameParts, 1))));
                } else {
                    $data['first_name'] = ucfirst(strtolower($name));
                }
                break;
            }
        }

        // Extract email
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $message, $matches)) {
            $data['email'] = $matches[0];
        }

        // Extract phone
        if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $message, $matches)) {
            $data['phone'] = $matches[0];
        }

        // Also try to extract phone with "phone" keyword
        if (preg_match('/phone\s+(\d{3}[-.]?\d{3}[-.]?\d{4})/i', $message, $matches)) {
            $data['phone'] = $matches[1];
        }

        // Try a more flexible phone pattern
        if (preg_match('/phone\s+([0-9\-]+)/i', $message, $matches)) {
            $data['phone'] = $matches[1];
        }

        // Extract company
        $companyPatterns = [
            'company ([a-zA-Z\s]+)',
            'works at ([a-zA-Z\s]+)',
            'from ([a-zA-Z\s]+)',
            'business ([a-zA-Z\s]+)'
        ];

        foreach ($companyPatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $message, $matches)) {
                $data['company_name'] = trim($matches[1]);
                break;
            }
        }

        return $data;
    }

    /**
     * Extract search criteria from read message
     */
    private function extractSearchCriteria(string $message): array
    {
        $criteria = [];

        // Extract name or email
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $message, $matches)) {
            $criteria['email'] = $matches[0];
                } else {
            // Extract potential name - look for capitalized words that could be names
            preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $message, $matches);
            if (!empty($matches[0])) {
                // Filter out common words that aren't names
                $commonWords = ['Show', 'Find', 'Get', 'View', 'Search', 'Look', 'Client', 'Who', 'Is', 'Tell', 'Me', 'About'];
                $potentialNames = array_filter($matches[0], function($word) use ($commonWords) {
                    return !in_array($word, $commonWords);
                });

                if (!empty($potentialNames)) {
                    $criteria['name'] = implode(' ', $potentialNames);
                }
            }

            // Alternative: look for "client [name]" pattern
            if (preg_match('/client\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i', $message, $matches)) {
                $criteria['name'] = trim($matches[1]);
            }
        }

        return $criteria;
    }

    /**
     * Extract update data from message
     */
    private function extractUpdateData(string $message): array
    {
        $data = [];

        // Extract identifier (name or email)
        $identifier = $this->extractSearchCriteria($message);
        if (!empty($identifier)) {
            $data['identifier'] = $identifier;
        }

        // Extract new values
        $updatePatterns = [
            'email to ([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Z|a-z]{2,})',
            'phone to (\d{3}[-.]?\d{3}[-.]?\d{4})',
            'name to ([a-zA-Z\s]+)',
            'company to ([a-zA-Z\s]+)'
        ];

        foreach ($updatePatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $message, $matches)) {
                $field = str_replace(['email to ', 'phone to ', 'name to ', 'company to '], '', $pattern);
                $data['updates'][$field] = trim($matches[1]);
            }
        }

        return $data;
    }

    /**
     * Extract list filters from message
     */
    private function extractListFilters(string $message): array
    {
        $filters = [];

        // Extract status filter
        if (str_contains($message, 'active')) {
            $filters['status'] = 'active';
        } elseif (str_contains($message, 'inactive')) {
            $filters['status'] = 'inactive';
        }

        // Extract industry filter
        $industryPatterns = [
            'technology',
            'healthcare',
            'finance',
            'retail',
            'manufacturing',
            'education'
        ];

        foreach ($industryPatterns as $industry) {
            if (str_contains($message, $industry)) {
                $filters['industry'] = $industry;
                break;
            }
        }

        // Extract search term
        if (preg_match('/search for ([a-zA-Z\s]+)/i', $message, $matches)) {
            $filters['search'] = trim($matches[1]);
        }

        return $filters;
    }

    /**
     * Calculate confidence score for intent detection
     */
    private function calculateConfidence(string $message, string $action): float
    {
        $confidence = 0.5; // Base confidence

        // Increase confidence based on specific keywords
        $actionKeywords = [
            'create' => ['create', 'add', 'new', 'register'],
            'read' => ['show', 'find', 'get', 'view', 'search', 'look'],
            'update' => ['update', 'edit', 'modify', 'change'],
            'delete' => ['delete', 'remove'],
            'list' => ['list', 'all', 'show all', 'directory']
        ];

        $keywords = $actionKeywords[$action] ?? [];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $confidence += 0.2;
            }
        }

        // Increase confidence if client-related keywords are present
        $clientKeywords = ['client', 'customer', 'contact'];
        foreach ($clientKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $confidence += 0.1;
            }
        }

        return min($confidence, 1.0);
    }

    /**
     * Check if message is providing additional information for client creation
     */
    private function isProvidingAdditionalInfo(string $message, array $previousContext): bool
    {
        // Check if previous context indicates we were asking for missing fields
        if (!isset($previousContext['missing_fields']) || empty($previousContext['missing_fields'])) {
            return false;
        }

        // Check if message contains information that could fill missing fields
        $hasEmail = preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $message);
        $hasName = preg_match('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $message);
        $hasPhone = preg_match('/\d{3}[-.]?\d{3}[-.]?\d{4}/', $message);

        // Also check for simple responses that might be names or emails
        $simpleResponse = trim($message);
        $isSimpleName = preg_match('/^[A-Z][a-z]+$/', $simpleResponse);
        $isSimpleEmail = preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$/', $simpleResponse);

        // Check if we have a current field being asked for
        $currentField = $previousContext['current_field'] ?? null;
        if ($currentField) {
            // If we're asking for a specific field, any non-empty response should be considered valid
            return !empty(trim($message)) && strlen(trim($message)) < 100; // Reasonable length for a field response
        }

        return $hasEmail || $hasName || $hasPhone || $isSimpleName || $isSimpleEmail;
    }

        /**
     * Extract additional client data from follow-up message
     */
    private function extractAdditionalClientData(string $message, array $previousContext): array
    {
        $data = [];
        $missingFields = $previousContext['missing_fields'] ?? [];
        $currentField = $previousContext['current_field'] ?? null;

        // If we have a current field being asked for, extract that specific field
        if ($currentField) {
            $data = $this->extractSpecificField($message, $currentField);
        } else {
            // Extract email if it was missing
            if (in_array('email', $missingFields)) {
                if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $message, $matches)) {
                    $data['email'] = $matches[0];
                }
            }

            // Extract name if it was missing
            if (in_array('first_name', $missingFields) || in_array('last_name', $missingFields)) {
                $nameData = $this->extractNameData($message, $missingFields);
                $data = array_merge($data, $nameData);
            }

            // Extract phone if it was missing
            if (in_array('phone', $missingFields)) {
                if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $message, $matches)) {
                    $data['phone'] = $matches[0];
                } elseif (preg_match('/phone\s+([0-9\-]+)/i', $message, $matches)) {
                    $data['phone'] = $matches[1];
                }
            }
        }

        // Merge with previous data
        if (!empty($previousContext['existing_data'])) {
            $data = array_merge($previousContext['existing_data'], $data);
        }

        return $data;
    }

    /**
     * Extract data for a specific field
     */
    private function extractSpecificField(string $message, string $field): array
    {
        $data = [];
        $message = trim($message);

        switch ($field) {
            case 'first_name':
                // Extract first name from simple response
                if (preg_match('/^[A-Z][a-z]+$/', $message)) {
                    $data['first_name'] = ucfirst(strtolower($message));
                } elseif (preg_match('/^([A-Z][a-z]+)\s+[A-Z][a-z]+$/', $message, $matches)) {
                    $data['first_name'] = ucfirst(strtolower($matches[1]));
                } else {
                    // If no pattern matches, treat the whole message as first name
                    $data['first_name'] = ucfirst(strtolower(trim($message)));
                }
                break;

            case 'last_name':
                // Extract last name from simple response
                if (preg_match('/^[A-Z][a-z]+$/', $message)) {
                    $data['last_name'] = ucfirst(strtolower($message));
                } elseif (preg_match('/^[A-Z][a-z]+\s+([A-Z][a-z]+)$/', $message, $matches)) {
                    $data['last_name'] = ucfirst(strtolower($matches[1]));
                } else {
                    // If no pattern matches, treat the whole message as last name
                    $data['last_name'] = ucfirst(strtolower(trim($message)));
                }
                break;

            case 'email':
                if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $message, $matches)) {
                    $data['email'] = $matches[0];
                }
                break;

            case 'phone':
                if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $message, $matches)) {
                    $data['phone'] = $matches[0];
                } elseif (preg_match('/phone\s+([0-9\-]+)/i', $message, $matches)) {
                    $data['phone'] = $matches[1];
                }
                break;
        }

        return $data;
    }

    /**
     * Extract name data from message
     */
    private function extractNameData(string $message, array $missingFields): array
    {
        $data = [];

        $namePatterns = [
            'name is ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'named ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'called ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'first name ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)',
            'last name ([a-zA-Z\s]+?)(?:\s+with|\s+email|\s+phone|$)'
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $message, $matches)) {
                $name = trim($matches[1]);
                $nameParts = explode(' ', $name);

                if (count($nameParts) >= 2) {
                    $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                    $data['last_name'] = ucfirst(strtolower(implode(' ', array_slice($nameParts, 1))));
                } else {
                    $data['first_name'] = ucfirst(strtolower($name));
                }
                break;
            }
        }

        // If no pattern matched, try to extract just names
        if (empty($data['first_name'])) {
            preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $message, $matches);
            if (!empty($matches[0])) {
                $commonWords = ['Show', 'Find', 'Get', 'View', 'Search', 'Look', 'Client', 'Who', 'Is', 'Tell', 'Me', 'About', 'The', 'Is', 'Are', 'Have', 'Has', 'Will', 'Can', 'Could', 'Would', 'Should'];
                $potentialNames = array_filter($matches[0], function($word) use ($commonWords) {
                    return !in_array($word, $commonWords);
                });

                if (!empty($potentialNames)) {
                    $name = implode(' ', $potentialNames);
                    $nameParts = explode(' ', $name);

                    if (count($nameParts) >= 2) {
                        $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                        $data['last_name'] = ucfirst(strtolower(implode(' ', array_slice($nameParts, 1))));
                    } else {
                        $data['first_name'] = ucfirst(strtolower($name));
                    }
                }
            }
        }

        // Handle simple responses (just a name)
        if (empty($data['first_name']) && empty($data['last_name'])) {
            $simpleResponse = trim($message);
            if (preg_match('/^[A-Z][a-z]+$/', $simpleResponse)) {
                // If we have a first name but need last name, or vice versa
                if (in_array('last_name', $missingFields)) {
                    $data['last_name'] = ucfirst(strtolower($simpleResponse));
                } elseif (in_array('first_name', $missingFields)) {
                    $data['first_name'] = ucfirst(strtolower($simpleResponse));
                }
            }
        }

        return $data;
    }
}
