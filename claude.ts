import {query} from '@anthropic-ai/claude-agent-sdk';

async function main() {
    const result = query({
        prompt: 'Review the following code for potential issues and suggest improvements:\n\n```javascript\nfunction add(a, b) {\n  return a + b;\n}\n\nconsole.log(add(2, 3));\n```',
        options: {
            allowedTools: ['ls', 'find', 'grep'],
            systemPrompt: 'You are a helpful coding assistant that reviews code snippets for potential issues and suggests improvements. Use AGENTS.md if present for context'
        }
    });

    result.setModel('claude-sonnet-4-20250514');

    console.log('output');
    for await (const message of result) {
        console.log(JSON.stringify(message, null, 2));
    }
    console.log('done');
}

main().catch(error => {
    console.error('Error:', error);
    process.exit(1);
});
