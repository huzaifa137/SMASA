{{-- Shared SMASA pagination CSS. Emitted once per page/response. --}}
@once
<style>
    .smasa-pager {
        --smasa-pg-primary: #2f2ccb;
        --smasa-pg-primary-soft: rgba(47, 44, 203, .10);
        --smasa-pg-text: #475569;
        --smasa-pg-muted: #94a3b8;
        --smasa-pg-surface: #fff;
        --smasa-pg-border: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .5rem;
        margin: .85rem 0 0;
        width: 100%;
    }

    /* Inside an existing card footer the footer already provides spacing */
    .pagination-container .smasa-pager,
    .pagination-wrapper .smasa-pager,
    .lib-card-footer .smasa-pager {
        margin: 0;
    }

    .smasa-pager__info {
        font-size: .78rem;
        color: var(--smasa-pg-muted);
    }

    .smasa-pager__list {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .25rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .smasa-pager__list .smasa-pager__link,
    .smasa-pager__list .smasa-pager__gap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 .5rem;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 600;
        line-height: 1;
        color: var(--smasa-pg-text);
        background: var(--smasa-pg-surface);
        border: 1px solid var(--smasa-pg-border);
        text-decoration: none;
        transition: all .15s;
    }

    .smasa-pager__list a.smasa-pager__link:hover {
        background: var(--smasa-pg-primary-soft);
        color: var(--smasa-pg-primary);
        border-color: rgba(47, 44, 203, .3);
        text-decoration: none;
    }

    .smasa-pager__list .is-active .smasa-pager__link {
        background: var(--smasa-pg-primary);
        color: #fff;
        border-color: var(--smasa-pg-primary);
    }

    .smasa-pager__list .is-disabled .smasa-pager__link {
        opacity: .4;
        pointer-events: none;
    }

    .smasa-pager__list .smasa-pager__gap {
        border-color: transparent;
        background: transparent;
        color: var(--smasa-pg-muted);
        min-width: 20px;
        padding: 0;
    }

    @media (max-width: 575.98px) {
        .smasa-pager {
            justify-content: center;
        }

        .smasa-pager__info {
            width: 100%;
            text-align: center;
        }

        .smasa-pager__list {
            justify-content: center;
        }
    }
</style>
@endonce
