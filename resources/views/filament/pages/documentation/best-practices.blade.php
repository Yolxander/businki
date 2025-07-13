<x-filament::card class="dark:bg-gray-900 dark:text-gray-100">
    <div class="prose max-w-none dark:prose-invert">
        <h2 class="text-xl font-semibold mb-4">Best Practices</h2>

        <div class="space-y-6">
            <!-- Prompt Engineering -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Prompt Engineering</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Be Specific and Detailed</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Provide comprehensive project descriptions with clear objectives</li>
                            <li>Include specific requirements, constraints, and preferences</li>
                            <li>Mention target audience, industry, and technical requirements</li>
                            <li>Specify desired tone, style, and format for generated content</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Use Contextual Information</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Include relevant background information about the client or project</li>
                            <li>Reference existing brand guidelines, style guides, or templates</li>
                            <li>Provide examples of similar projects or desired outcomes</li>
                            <li>Include budget constraints and timeline requirements</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Parameter Optimization -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Parameter Optimization</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Temperature Settings</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li><strong>0.1-0.3:</strong> Use for factual, consistent content (proposals, documentation)</li>
                            <li><strong>0.4-0.7:</strong> Balanced creativity and consistency (project plans, task descriptions)</li>
                            <li><strong>0.8-1.2:</strong> High creativity for brainstorming and ideation</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Token Management</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Set appropriate max_tokens to control response length and costs</li>
                            <li>Use shorter prompts for simple tasks to save tokens</li>
                            <li>Break complex requests into smaller, focused calls</li>
                            <li>Monitor token usage to optimize cost-effectiveness</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Error Handling -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Error Handling & Reliability</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Implement Proper Error Handling</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Always check response status codes before processing data</li>
                            <li>Implement retry logic for transient failures (5xx errors)</li>
                            <li>Handle rate limiting gracefully with exponential backoff</li>
                            <li>Log errors for debugging and monitoring</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Validation Best Practices</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Validate all input parameters before sending requests</li>
                            <li>Check for required fields and data types</li>
                            <li>Implement client-side validation for better UX</li>
                            <li>Provide clear error messages to users</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Performance Optimization -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Performance Optimization</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Request Optimization</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Batch related requests when possible to reduce API calls</li>
                            <li>Cache frequently requested content to avoid regeneration</li>
                            <li>Use appropriate HTTP methods and status codes</li>
                            <li>Implement request deduplication for identical prompts</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Response Processing</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Process responses asynchronously for better user experience</li>
                            <li>Implement progress indicators for long-running operations</li>
                            <li>Store generated content for future reference and reuse</li>
                            <li>Optimize response parsing and data transformation</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Security Considerations -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Security Considerations</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Authentication & Authorization</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Always use secure authentication tokens</li>
                            <li>Implement proper token rotation and expiration</li>
                            <li>Validate user permissions before processing requests</li>
                            <li>Use HTTPS for all API communications</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Data Protection</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Sanitize user inputs to prevent injection attacks</li>
                            <li>Avoid sending sensitive data in prompts</li>
                            <li>Implement proper data encryption for stored content</li>
                            <li>Follow GDPR and privacy regulations for user data</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content Quality -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Content Quality Assurance</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium mb-2">Review and Refinement</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Always review generated content before using it</li>
                            <li>Fact-check important information and claims</li>
                            <li>Ensure content aligns with brand voice and guidelines</li>
                            <li>Edit and refine content for clarity and accuracy</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Iterative Improvement</h4>
                        <ul class="text-sm space-y-1 list-disc pl-6">
                            <li>Use feedback loops to improve prompt effectiveness</li>
                            <li>Track which prompts produce the best results</li>
                            <li>Refine prompts based on user feedback and outcomes</li>
                            <li>Maintain a library of effective prompts for reuse</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="text-lg font-medium mb-2">Quick Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <ul class="space-y-1">
                    <li>• Start with lower temperature for consistent results</li>
                    <li>• Test prompts with small datasets first</li>
                    <li>• Keep prompts concise but informative</li>
                    <li>• Use specific examples in your prompts</li>
                </ul>
                <ul class="space-y-1">
                    <li>• Monitor API usage and costs regularly</li>
                    <li>• Implement proper error handling</li>
                    <li>• Cache successful results when possible</li>
                    <li>• Always validate generated content</li>
                </ul>
            </div>
        </div>
    </div>
</x-filament::card>
