import sys

anti_inspect_script = """
    <!-- Anti-Inspect Element Script -->
    <script>
        // Disable Right Click
        document.addEventListener('contextmenu', event => event.preventDefault());
        
        // Disable Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            // Disable F12
            if(e.keyCode == 123) {
                e.preventDefault();
                return false;
            }
            // Disable Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
            if(e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0) || e.keyCode == 'C'.charCodeAt(0))) {
                e.preventDefault();
                return false;
            }
            // Disable Ctrl+U (View Source)
            if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
"""

def append_script(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if '<!-- Anti-Inspect Element Script -->' in content:
        print(f"Already applied to {filepath}")
        return
        
    # Replace </body>\n</html> with the script
    if '</body>\n</html>' in content:
        content = content.replace('</body>\n</html>', anti_inspect_script)
    elif '</body>' in content:
        content = content.replace('</body>', anti_inspect_script.replace('\n</html>', ''))
        
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Applied to {filepath}")

for arg in sys.argv[1:]:
    append_script(arg)
