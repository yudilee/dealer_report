<div id="password-overlay" style="display: none;">
    <div class="password-modal">
        <h2><span style="font-size: 1.5rem;">🔒</span> Security Check</h2>
        <p>Please enter the access code to view this dashboard.</p>
        
        <div class="input-group">
            <input type="password" id="password-input" placeholder="Enter access code" autocomplete="current-password">
            <button id="submit-password">Access</button>
        </div>
        <p id="password-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 10px; display: none;">
            Incorrect access code. Please try again.
        </p>
    </div>
</div>

<style>
    #password-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .password-modal {
        background: var(--bg-card, #1e293b);
        padding: 2rem;
        border-radius: 1rem;
        border: 1px solid var(--border-color, #334155);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        max-width: 400px;
        width: 90%;
        text-align: center;
    }

    .password-modal h2 {
        color: var(--text-primary, #f1f5f9);
        margin-bottom: 0.5rem;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .password-modal p {
        color: var(--text-secondary, #94a3b8);
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .input-group {
        display: flex;
        gap: 0.5rem;
    }

    #password-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color, #334155);
        background: var(--bg-dark, #0f172a);
        color: var(--text-primary, #f1f5f9);
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
    }

    #password-input:focus {
        border-color: var(--accent-blue, #3b82f6);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    #submit-password {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        background: var(--accent-blue, #3b82f6);
        color: white;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    #submit-password:hover {
        background: #2563eb;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('password-overlay');
        const input = document.getElementById('password-input');
        const submitBtn = document.getElementById('submit-password');
        const errorMsg = document.getElementById('password-error');
        
        // Hardcoded password
        const ACCESS_CODE = 'b4RgP9Em@d85';
        const STORAGE_KEY = 'dealer_reports_access';
        
        // Check if already authenticated
        if (!localStorage.getItem(STORAGE_KEY)) {
            overlay.style.display = 'flex';
            input.focus();
        }

        function checkPassword() {
            if (input.value === ACCESS_CODE) {
                localStorage.setItem(STORAGE_KEY, 'true');
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            } else {
                errorMsg.style.display = 'block';
                input.value = '';
                input.focus();
                
                // Shake animation
                const modal = document.querySelector('.password-modal');
                modal.animate([
                    { transform: 'translateX(0)' },
                    { transform: 'translateX(-10px)' },
                    { transform: 'translateX(10px)' },
                    { transform: 'translateX(-10px)' },
                    { transform: 'translateX(10px)' },
                    { transform: 'translateX(0)' }
                ], {
                    duration: 400
                });
            }
        }

        submitBtn.addEventListener('click', checkPassword);

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkPassword();
            }
        });
    });
</script>
