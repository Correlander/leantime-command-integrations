(function () {
    'use strict';

    function registerAiCommand() {
        if (!window.leantime || !window.leantime.tiptapController) {
            return false;
        }

        window.leantime.tiptapController.registerSlashCommand('ai', {
            label: 'AI: Generate User Story',
            description: 'Turn the current description into a structured user story',
            icon: '<i class="fa-solid fa-wand-magic-sparkles"></i>',
            action: async function (editor) {
                const source = editor.getText();
                if (!source.trim()) return;

                editor.setEditable(false);
                try {
                    const response = await fetch(window.leantime.appUrl + '/aiCommands/ai/generate', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ text: source, operation: 'user-story' })
                    });

                    if (!response.ok) throw new Error('AI request failed (' + response.status + ')');
                    const result = await response.json();
                    if (!result.content) throw new Error('AI response did not contain content');
                    editor.commands.setContent(result.content);
                } catch (error) {
                    console.error('[AiCommands]', error);
                } finally {
                    editor.setEditable(true);
                    editor.commands.focus('end');
                }
            }
        });

        return true;
    }

    // Load from Leantime's header hook so registration runs before ready callbacks
    // create rich editors. Leantime builds each editor's slash-command list once.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', registerAiCommand, { once: true });
    } else {
        registerAiCommand();
    }
})();
