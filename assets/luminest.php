<style>
    :root {
        --lm-red: #c1121f;
        --lm-blue: #1d4ed8;
        --lm-ink: #111827;
        --lm-soft: #eff6ff;
    }

    .lm-auth-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 10% 10%, rgba(29, 78, 216, 0.12), transparent 32%),
            radial-gradient(circle at 90% 12%, rgba(193, 18, 31, 0.12), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        display: flex;
        align-items: center;
        padding: 2rem 0;
    }

    .lm-auth-card {
        border: 1px solid #dbeafe;
        border-radius: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .lm-auth-logo {
        width: 72px;
        height: 72px;
        object-fit: contain;
    }

    .lm-auth-badge {
        background: var(--lm-soft);
        color: var(--lm-blue);
        border: 1px solid #bfdbfe;
    }

    .lm-title {
        color: var(--lm-ink);
    }

    .lm-title span {
        color: var(--lm-red);
    }

    .lm-form-control {
        border-radius: 0.75rem;
        border-color: #d1d5db;
        padding: 0.7rem 0.85rem;
    }

    .lm-form-control:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.2rem rgba(29, 78, 216, 0.15);
    }

    .lm-btn-primary {
        background: linear-gradient(135deg, var(--lm-red), #8f0f18);
        border-color: var(--lm-red);
        color: #fff;
        border-radius: 0.75rem;
        font-weight: 700;
    }

    .lm-btn-primary:hover {
        color: #fff;
    }

    .lm-muted-link {
        color: var(--lm-blue);
        text-decoration: none;
        font-weight: 600;
    }

    .lm-muted-link:hover {
        color: #1e40af;
    }
</style>