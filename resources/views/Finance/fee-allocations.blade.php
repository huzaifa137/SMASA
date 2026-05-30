@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --g: #2f2ccb;
            --gl: rgba(47, 44, 203, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --grn: #059669;
            --grnl: rgba(5, 150, 105, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* Hero */
        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .fin-hero p {
            color: #c7d2fe;
            margin: .2rem 0 0;
            font-size: .88rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(47, 44, 203, .25);
            border: 1px solid rgba(165, 180, 252, .4);
            color: #a5b4fc;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .6rem;
        }

        /* Cards */
        .fin-card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            box-shadow: var(--sh);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .fin-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
        }

        .fin-card-header h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            padding: 1.2rem;
            text-align: center;
            transition: all .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sh);
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--t1);
            font-family: 'DM Mono', monospace;
        }

        .stat-card .label {
            font-size: .75rem;
            color: var(--t3);
            margin-top: .3rem;
            font-weight: 500;
        }

        .stat-card .sub {
            font-size: .7rem;
            margin-top: .2rem;
        }

        /* Buttons */
        .btn-fin {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: var(--rads);
            font-size: .875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-sm {
            padding: .4rem .85rem;
            font-size: .8rem;
        }

        .btn-primary-fin {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary-fin:hover {
            background: #2420a8;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
        }

        .btn-outline-fin {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2);
        }

        .btn-outline-fin:hover {
            border-color: #2f2ccb;
            color: #2f2ccb;
        }

        .btn-secondary-fin {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-secondary-fin:hover {
            background: #2420a8;
        }

        /* Filters */
        /* Update the filters section */
        /* Improved Filters Section - Mobile Responsive */
        .filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
            padding: 0 1.5rem 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            flex: 1;
            min-width: 140px;
        }

        .filter-group label {
            font-size: .7rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .filter-group select,
        .filter-group input {
            padding: .6rem .85rem;
            border-radius: 10px;
            border: 1.5px solid var(--brd);
            font-size: .85rem;
            background: var(--surf);
            transition: all .15s;
            width: 100%;
            cursor: pointer;
        }

        /* Responsive styles for filters */
        @media (max-width: 768px) {
            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .fin-card-header .btn-fin {
                width: 100%;
                justify-content: center;
            }

            .filters {
                flex-direction: column;
                gap: 0.75rem;
                padding: 0 1rem 1rem;
            }

            .filter-group {
                width: 100%;
                min-width: auto;
            }

            .filter-group label {
                font-size: 0.7rem;
            }

            .filter-group select,
            .filter-group input {
                padding: 0.55rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .filters {
                padding: 0 0.75rem 0.75rem;
            }

            .filter-group {
                gap: 0.3rem;
            }
        }

        /* Ensure the card header button doesn't overflow */
        .fin-card-header .btn-fin {
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .fin-card-header .btn-fin {
                white-space: normal;
                text-align: center;
                font-size: 0.75rem;
                padding: 0.5rem 0.75rem;
            }

            .fin-card-header h3 {
                font-size: 0.85rem;
            }
        }

        /* Update the fin-card-header to better align with filters */
        .fin-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Make the button not shrink */
        .fin-card-header .btn-fin {
            flex-shrink: 0;
        }

        .filter-group select,
        .filter-group input {
            padding: .5rem .75rem;
            border-radius: 8px;
            border: 1.5px solid var(--brd);
            font-size: .85rem;
            background: var(--surf);
            transition: all .15s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        /* Badges */
        .badge-fin {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .7rem;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 600;
        }

        .badge-green {
            background: var(--grnl);
            color: var(--grn);
        }

        .badge-red {
            background: var(--rl);
            color: var(--r);
        }

        .badge-amber {
            background: var(--al);
            color: var(--a);
        }

        .badge-blue {
            background: var(--bl);
            color: var(--b);
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--t2);
        }

        /* Force horizontal scrollbar to always be visible on tables that need it */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #2c29ca;
            padding: .8rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
        }

        .data-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .data-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .data-table td {
            padding: .9rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .85rem;
            color: var(--t1);
            vertical-align: middle;
        }

        /* Update the empty state styling */
        .data-table td[colspan="8"] {
            text-align: center;
            padding: 3rem;
        }

        .data-table td[colspan="8"] i {
            font-size: 2rem;
            opacity: .3;
            display: block;
            margin-bottom: .5rem;
        }

        .data-table td[colspan="8"] p {
            margin: 0 0 1rem 0;
        }

        .data-table td[colspan="8"] .btn-fin {
            display: inline-flex;
            margin-top: 1rem;
        }

        .data-table tr:hover td {
            background: #f5f6ff;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Amount styling */
        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        /* Progress bar */
        .progress-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 4px;
            width: 60px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }



        .modal-body {
            padding-top: 1rem;
            flex: 1;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--t1);
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--t3);
            transition: color .2s;
        }

        .close-modal:hover {
            color: var(--r);
        }

        /* Responsive */
        @media(max-width:900px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:600px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }

            .fin-hero {
                padding: 1.5rem;
            }

            .filters {
                flex-direction: column;
            }
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--surf);
            border-radius: 20px;
            max-width: 550px;
            width: 90%;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            background: #2f2ccb !important;
            border-radius: 20px 20px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--t1);
            display: flex;
            align-items: center;
            color: #fff;
            gap: 0.5rem;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--t3);
            transition: color 0.2s;
            line-height: 1;
        }

        .close-modal:hover {
            color: #c7d2fe;
        }

        .modal-form-group {
            padding: 0 1.5rem;
            margin-bottom: 1.25rem;
        }

        .modal-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .modal-label i {
            color: #2f2ccb;
            margin-right: 0.3rem;
        }

        .modal-select,
        .modal-input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid var(--brd);
            border-radius: 10px;
            font-size: 0.875rem;
            color: var(--t1);
            background: var(--surf);
            transition: all 0.15s;
            outline: none;
        }

        .modal-select:focus,
        .modal-input:focus {
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, 0.1);
        }

        .modal-select[multiple] {
            min-height: 130px;
            padding: 0.5rem;
        }

        .modal-select[multiple] option {
            padding: 0.5rem;
            border-radius: 6px;
            margin-bottom: 2px;
        }

        .modal-select[multiple] option:hover {
            background: var(--bl);
        }

        .modal-hint {
            display: block;
            font-size: 0.7rem;
            color: var(--t3);
            margin-top: 0.4rem;
        }

        .modal-hint i {
            margin-right: 0.2rem;
        }

        .modal-form-row {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 1rem;
            padding: 0 1.5rem 1.5rem;
            align-items: start;
        }

        .modal-form-row .modal-form-group {
            margin-bottom: 0;
            padding: 0;
        }

        .discount-card {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 1rem;
            transition: all .2s ease;
        }

        .discount-card:hover {
            border-color: rgba(47, 44, 203, .25);
            background: #fbfcff;
        }

        .discount-card .modal-label {
            margin-bottom: .6rem;
        }

        .discount-card .modal-input {
            background: #fff;
        }

        /* Mobile responsiveness */
        @media(max-width: 700px) {
            .modal-form-row {
                grid-template-columns: 1fr;
            }
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--brd);
            background: #fafbff;
            border-radius: 0 0 20px 20px;
        }

        .student-selector {
            max-height: 260px;
            overflow-y: auto;

            border: 1.5px solid var(--brd);
            border-radius: 14px;
            padding: 1rem;
            background: #fff;

            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .8rem;
            margin-top: .25rem;
        }

        .student-chip {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: .9rem 1rem;
            cursor: pointer;
            transition: all .18s ease;
            user-select: none;

            display: flex;
            align-items: center;
            gap: .75rem;

            min-height: 72px;
        }

        .student-chip:hover {
            border-color: #2f2ccb;
            background: #f5f6ff;
            transform: translateY(-1px);
        }

        .student-chip.selected {
            background: linear-gradient(135deg, #2f2ccb 0%, #4338ca 100%);
            color: #fff;
            border-color: #2f2ccb;
            box-shadow: 0 8px 20px rgba(47, 44, 203, .22);
        }

        .student-chip i {
            font-size: 1rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(47, 44, 203, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2f2ccb;
            flex-shrink: 0;
        }

        .student-chip.selected i {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .student-chip .student-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .student-chip .student-name {
            font-size: .86rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .student-chip .student-adm {
            font-size: .72rem;
            opacity: .75;
            margin-top: .2rem;
        }

        .modal-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-actions {
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .student-selector::-webkit-scrollbar,
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .student-selector::-webkit-scrollbar-thumb,
        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 20px;
        }

        .student-selector::-webkit-scrollbar-thumb:hover,
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Fix for Select Students label visibility */
        .modal-form-group>.modal-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: .75rem;
            font-size: .8rem;
            font-weight: 700;
            color: #2f2ccb;
            letter-spacing: .04em;
            position: relative;
            z-index: 1;
        }

        /* Ensure the modal-body has proper spacing */
        .modal-body {
            padding-top: 0.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* Make sure the student selector section is visible */
        .student-selector {
            max-height: 260px;
            overflow-y: auto;
            border: 1.5px solid var(--brd);
            border-radius: 14px;
            padding: 1rem;
            background: #fff;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .8rem;
            margin-top: 0.5rem;
        }

        /* Modal selector cards */
        .selector-card {
            border: 1.5px solid var(--brd);
            border-radius: 10px;
            padding: 0.6rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--surf);
        }

        .selector-card:hover {
            border-color: #2f2ccb;
            transform: translateY(-2px);
        }

        .selector-card.selected {
            border-color: #2f2ccb;
            background: var(--bl);
        }

        .selector-card.selected .sc-label {
            color: #2f2ccb;
            font-weight: 700;
        }

        .student-list-modal {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid var(--brd);
            border-radius: 10px;
        }

        .student-checkbox-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid #f8fafc;
            cursor: pointer;
            transition: background 0.15s;
        }

        .student-checkbox-row:hover {
            background: #f8fafc;
        }

        .student-checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2f2ccb;
        }

        .student-checkbox-row .student-info {
            flex: 1;
        }

        .student-checkbox-row .student-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--t1);
        }

        .student-checkbox-row .student-adm {
            font-size: 0.7rem;
            color: var(--t3);
        }

        /* Action buttons in table */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-icon-edit {
            background: rgba(47, 44, 203, 0.1);
            color: #2f2ccb;
        }

        .btn-icon-edit:hover {
            background: #2f2ccb;
            color: #fff;
            transform: scale(1.05);
        }

        .btn-icon-delete {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
        }

        .btn-icon-delete:hover {
            background: #dc2626;
            color: #fff;
            transform: scale(1.05);
        }

        /* Table wrapper with beautiful scrollbar */
        .table-wrapper {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            margin: 0;
            border-radius: var(--rads);
            width: 100%;
            /* Ensure wrapper takes full width */
        }

        /* Custom Scrollbar Styling */
        .table-wrapper::-webkit-scrollbar {
            height: 10px;
            width: 10px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
            margin: 0 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #2f2ccb;
        }

        /* Firefox scrollbar */
        .table-wrapper {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        /* Table styling - ensure scroll works */
        .data-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .data-table th,
        .data-table td {
            white-space: nowrap;
            padding: 0.9rem 1rem;
        }

        /* For medium screens, reduce min-width */
        @media (max-width: 1199px) and (min-width: 769px) {
            .data-table {
                min-width: 900px;
            }
        }

        /* For mobile, ensure scroll is smooth and scrollbar visible */
        @media (max-width: 768px) {
            .table-wrapper {
                margin: 0 -0.5rem;
                padding: 0 0.5rem;
                /* Ensure scrollbar appears */
                overflow-x: auto;
            }

            .data-table {
                min-width: 750px;
            }

            .data-table th,
            .data-table td {
                padding: 0.7rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* Ensure scrollbar is visible on all devices */
        .table-wrapper {
            scrollbar-width: auto;
        }

        .table-wrapper::-webkit-scrollbar {
            display: block;
        }

        /* Scrollbar track and thumb styling */
        .table-wrapper::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #2f2ccb;
            border-radius: 8px;
            cursor: pointer;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #2420a8;
        }

        /* Scroll indicator hint */
        .scroll-hint {
            display: flex;
            text-align: center;
            font-size: 0.7rem;
            color: var(--t3);
            margin-top: 0.75rem;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .scroll-hint i {
            font-size: 0.6rem;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateX(0);
                opacity: 0.5;
            }

            50% {
                transform: translateX(3px);
                opacity: 1;
            }
        }

        /* Hide scroll hint on desktop where scroll isn't needed */
        @media (min-width: 1200px) {
            .scroll-hint {
                display: none;
            }
        }

        /* For medium screens, keep minimal width but allow scroll */
        @media (max-width: 1199px) and (min-width: 769px) {
            .data-table {
                min-width: 900px;
            }
        }

        /* For mobile, ensure scroll is smooth */
        @media (max-width: 768px) {
            .table-wrapper {
                margin: 0 -0.5rem;
                padding: 0 0.5rem;
            }

            .data-table {
                min-width: 750px;
            }

            .data-table th,
            .data-table td {
                padding: 0.7rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* Scroll indicator hint using CSS pseudo-element */
        .scroll-hint {
            display: none;
            text-align: center;
            font-size: 0.7rem;
            color: var(--t3);
            margin-top: 0.5rem;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .scroll-hint i {
            font-size: 0.6rem;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateX(0);
                opacity: 0.5;
            }

            50% {
                transform: translateX(3px);
                opacity: 1;
            }
        }

        @media (max-width: 1199px) {
            .scroll-hint {
                display: flex;
            }
        }

        /* Beautiful Pagination Styling */
        .fin-card .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 0;
            padding: 0.75rem 0;
        }

        .fin-card .pagination li {
            list-style: none;
            display: inline-block;
            margin: 0;
        }

        .fin-card .pagination a,
        .fin-card .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 0.75rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        /* Active page */
        .fin-card .pagination .active span {
            background: #2f2ccb;
            color: #fff;
            border: none;
            box-shadow: 0 2px 8px rgba(47, 44, 203, 0.3);
        }

        /* Inactive links */
        .fin-card .pagination a {
            background: transparent;
            color: var(--t2);
            border: 1.5px solid var(--brd);
        }

        .fin-card .pagination a:hover {
            background: rgba(47, 44, 203, 0.08);
            border-color: #2f2ccb;
            color: #2f2ccb;
            transform: translateY(-1px);
        }

        /* Disabled (Previous/Next when inactive) */
        .fin-card .pagination .disabled span {
            background: #f8fafc;
            color: var(--t3);
            border: 1.5px solid var(--brd);
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Ellipsis dots */
        .fin-card .pagination .dots span {
            background: transparent;
            border: none;
            color: var(--t3);
            cursor: default;
            letter-spacing: 2px;
        }

        /* Pagination container wrapper */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--brd);
            background: #fafbff;
            border-radius: 0 0 var(--rad) var(--rad);
        }

        .pagination-info {
            font-size: 0.8rem;
            color: var(--t3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-info i {
            font-size: 0.75rem;
            color: #2f2ccb;
        }

        /* Responsive pagination */
        @media (max-width: 600px) {
            .pagination-wrapper {
                flex-direction: column;
                justify-content: center;
                text-align: center;
            }

            .fin-card .pagination a,
            .fin-card .pagination span {
                min-width: 32px;
                height: 32px;
                padding: 0 0.6rem;
                font-size: 0.75rem;
            }

            .fin-card .pagination {
                gap: 0.35rem;
            }
        }

        /* Custom Pagination Styling */
        .custom-pagination {
            margin-top: 1rem;
        }

        .pagination-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 38px;
            height: 38px;
            padding: 0 0.85rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            background: transparent;
            color: var(--t2);
            border: 1.5px solid var(--brd);
        }

        .pagination-link i {
            font-size: 0.7rem;
        }

        .pagination-text {
            display: inline-block;
        }

        /* Active page */
        .pagination-link.active {
            background: #2f2ccb;
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(47, 44, 203, 0.35);
        }

        /* Hover effect for links */
        .pagination-link:not(.active):not(.disabled):hover {
            background: rgba(47, 44, 203, 0.08);
            border-color: #2f2ccb;
            color: #2f2ccb;
            transform: translateY(-2px);
        }

        /* Disabled state (Prev/Next when no more pages) */
        .pagination-link.disabled {
            background: #f8fafc;
            color: var(--t3);
            border: 1.5px solid var(--brd);
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
        }

        /* Pagination dots */
        .pagination-dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            color: var(--t3);
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 2px;
        }

        /* Pagination info bar - update existing */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--brd);
            background: #fafbff;
            border-radius: 0 0 var(--rad) var(--rad);
        }

        .pagination-info {
            font-size: 0.8rem;
            color: var(--t3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #fff;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            border: 1px solid var(--brd);
        }

        .pagination-info i {
            font-size: 0.75rem;
            color: #2f2ccb;
        }

        /* Hide text on mobile if needed */
        @media (max-width: 640px) {
            .pagination-text {
                display: none;
            }

            .pagination-link {
                min-width: 36px;
                height: 36px;
                padding: 0;
            }

            .pagination-link i {
                margin: 0;
            }

            .pagination-wrapper {
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .pagination-info {
                width: 100%;
                justify-content: center;
            }

            .pagination-links {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .pagination-link {
                min-width: 32px;
                height: 32px;
                font-size: 0.75rem;
                border-radius: 8px;
            }

            .pagination-dots {
                min-width: 32px;
                height: 32px;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-user-graduate"></i> Finance — Fee Allocations</div>
            <h1>Student Fee Allocations</h1>
            <p>Assign fee structures to students, manage discounts and track payment status</p>
        </div>
    </div>
@endsection

@section('content')
    {{-- Stats Summary --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">UGX {{ number_format($summary->total_billed ?? 0, 0) }}</div>
            <div class="label">Total Billed</div>
            <div class="sub" style="color:var(--t3);">All students combined</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($summary->total_collected ?? 0, 0) }}</div>
            <div class="label">Total Collected</div>
            <div class="sub" style="color:var(--b);">✓ Collection rate</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($summary->total_outstanding ?? 0, 0) }}</div>
            <div class="label">Outstanding Balance</div>
            <div class="sub" style="color:var(--r);">Pending payments</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $summary->total_students ?? 0 }}</div>
            <div class="label">Students with Allocations</div>
            <div class="sub" style="color:var(--t3);">Active allocations</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-filter"></i> Filters & Bulk Allocation</h3>
            <button class="btn-fin btn-primary-fin" onclick="openAllocateModal()">
                <i class="fas fa-plus"></i> Bulk Allocate Fees
            </button>
        </div>
        <div class="filters">
            <div class="filter-group">
                <label>Academic Year</label>
                <select name="year" id="filterYear" onchange="applyFilters()">
                    <option value="{{ date('Y') }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                    <option value="{{ date('Y') - 1 }}" {{ $year == date('Y') - 1 ? 'selected' : '' }}>{{ date('Y') - 1 }}
                    </option>
                    <option value="{{ date('Y') - 2 }}" {{ $year == date('Y') - 2 ? 'selected' : '' }}>{{ date('Y') - 2 }}
                    </option>
                </select>
            </div>
            <div class="filter-group">
                <label>Term</label>
                <select name="term" id="filterTerm" onchange="applyFilters()">
                    <option value="">All Terms</option>
                    <option value="1" {{ $term == '1' ? 'selected' : '' }}>Term 1</option>
                    <option value="2" {{ $term == '2' ? 'selected' : '' }}>Term 2</option>
                    <option value="3" {{ $term == '3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Payment Status</label>
                <select id="filterStatus" onchange="applyFilters()">
                    <option value="">All</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Allocations Table --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-list"></i> Fee Allocations</h3>
            <span style="font-size:.75rem;color:var(--t3);">{{ $allocations->total() }} records</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Fee Structure</th>
                        <th>Year/Term</th>
                        <th>Allocated Amount</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Paid / Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $alloc)
                                        @php
                                            $net = $alloc->allocated_amount - $alloc->discount_amount;
                                            $paid = $net - $alloc->balance;
                                            $percent = $net > 0 ? round(($paid / $net) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div style="font-weight:600;">{{ $alloc->student->firstname ?? 'N/A' }}
                                                    {{ $alloc->student->lastname ?? '' }}
                                                </div>
                                                <div style="font-size:.7rem;color:var(--t3);">ADM:
                                                    {{ $alloc->student->admission_number ?? '—' }}
                                                </div>
                            </div>
                            <td>
                                <div>{{ $alloc->feeStructure->name ?? '—' }}</div>
                                <div style="font-size:.7rem;color:var(--t3);">
                                    {{ $alloc->feeStructure->class_level ?? 'All levels' }}
                                </div>
                        </div>
                        <td>
                            <span class="badge-fin badge-blue">{{ $alloc->academic_year }}</span>
                            <span class="badge-fin badge-gray">Term {{ $alloc->term }}</span>
                            </div>
                        <td class="amount-mono">UGX {{ number_format($alloc->allocated_amount, 0) }}</td>
                        <td class="amount-mono" style="color:var(--r);">
                            @if($alloc->discount_amount > 0)
                                - UGX {{ number_format($alloc->discount_amount, 0) }}
                                @if($alloc->discount_reason)
                                    <div style="font-size:.65rem;">{{ $alloc->discount_reason }}</div>
                                @endif
                            @else —
                            @endif
                        </td>
                        <td class="amount-mono" style="font-weight:700;">UGX {{ number_format($net, 0) }}</td>
                        <td>
                            <div class="amount-mono" style="color:var(--b);">UGX {{ number_format($paid, 0) }}</div>
                            <div class="amount-mono" style="color:var(--r);">UGX {{ number_format($alloc->balance, 0) }}
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill"
                                    style="width: {{ $percent }}%; background: {{ $percent >= 100 ? '#059669' : '#2f2ccb' }};">
                                </div>
                            </div>
                            </div>
                        <td>
                            @if($alloc->payment_status == 'paid')
                                <span class="badge-fin badge-green"><i class="fas fa-check-circle"></i> Paid</span>
                            @elseif($alloc->payment_status == 'partial')
                                <span class="badge-fin badge-amber"><i class="fas fa-hourglass-half"></i> Partial</span>
                            @else
                                <span class="badge-fin badge-red"><i class="fas fa-times-circle"></i> Unpaid</span>
                            @endif
                            </div>
                        <td class="action-buttons">
                            <button type="button" class="btn-icon btn-icon-edit" onclick="editAllocation({{ $alloc->id }})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-icon btn-icon-delete" onclick="deleteAllocation({{ $alloc->id }})" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                        </tr>
                    @empty
        <tr>
            <td colspan="8" style="text-align:center;padding:3rem;">
                <i class="fas fa-inbox" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p style="margin:0 0 1rem 0;">No fee allocations found.</p>
                <button class="btn-fin btn-primary-fin" onclick="openAllocateModal()">
                    <i class="fas fa-plus"></i> Create First Allocation
                </button>
            </td>
        </tr>
    @endforelse
    </tbody>
    </table>
    </div>

    <div class="scroll-hint">
        <i class="fas fa-arrows-alt-h"></i>
        <span>Swipe or scroll horizontally to see more columns</span>
        <i class="fas fa-arrows-alt-h"></i>
    </div>

    @if($allocations->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                <i class="fas fa-table-list"></i>
                Showing {{ $allocations->firstItem() ?? 0 }} to {{ $allocations->lastItem() ?? 0 }} of
                {{ $allocations->total() }} records
            </div>
            <div>
                {{ $allocations->appends(['year' => $year, 'term' => $term])->links('vendor.pagination.custom') }}
            </div>
        </div>
    @endif
    </div>

    {{-- Bulk Allocation Modal --}}
    <div id="allocateModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-layer-group"></i> Bulk Allocate Fee Structure</h3>
                <span class="close-modal" onclick="closeAllocateModal()">&times;</span>
            </div>
            <form method="POST" action="{{ route('finance.allocate-fees') }}" id="bulkAllocationForm">
                @csrf
                <div class="modal-body">
                    {{-- Step 1: Select Class --}}
                    <div class="modal-form-group" id="modalStepClass">
                        <label class="modal-label">
                            <i class="fas fa-chalkboard"></i> 1. Select Class
                        </label>
                        <div class="selector-grid" id="modalClassGrid"
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                            @foreach($classrooms ?? [] as $cls)
                                @php $clsName = \App\Http\Controllers\Helper::recordMdname($cls->class_name) ?? $cls->class_name; @endphp
                                <div class="selector-card" data-class-id="{{ $cls->class_name }}"
                                    data-class-name="{{ $clsName }}" onclick="selectModalClass(this)"
                                    style="padding: 0.6rem; text-align: center; cursor: pointer;">
                                    <div class="sc-icon"><i class="fas fa-university"></i></div>
                                    <div class="sc-label" style="font-size: 0.7rem;">{{ $clsName }}</div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="modalSelectedClassId" name="selected_class_id">
                    </div>

                    {{-- Step 2: Select Stream --}}
                    <div class="modal-form-group" id="modalStepStream" style="display: none;">
                        <label class="modal-label">
                            <i class="fas fa-code-branch"></i> 2. Select Stream
                        </label>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; color: var(--t2);">Class: </span>
                            <span id="modalChosenClassName"
                                style="font-size: 0.75rem; font-weight: 700; color: #2f2ccb;"></span>
                            <button type="button" onclick="resetModalToClass()"
                                style="background: none; border: none; color: var(--r); font-size: 0.7rem; cursor: pointer; margin-left: 0.5rem;">
                                <i class="fas fa-times"></i> Change
                            </button>
                        </div>
                        <div class="selector-grid" id="modalStreamGrid"
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                            <div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading streams...</div>
                        </div>
                        <input type="hidden" id="modalSelectedStreamId" name="selected_stream_id">
                    </div>

                    {{-- Step 3: Select Students (Chips UI) --}}
                    <div class="modal-form-group" id="modalStepStudents" style="display: none;">
                        <label class="modal-label">
                            <i class="fas fa-users"></i> 3. Select Students
                        </label>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; color: var(--t2);">Stream: </span>
                            <span id="modalChosenStreamName"
                                style="font-size: 0.75rem; font-weight: 700; color: #2f2ccb;"></span>
                            <button type="button" onclick="resetModalToStream()"
                                style="background: none; border: none; color: var(--r); font-size: 0.7rem; cursor: pointer; margin-left: 0.5rem;">
                                <i class="fas fa-times"></i> Change
                            </button>
                        </div>
                        <div style="position: relative; margin-bottom: 0.5rem;">
                            <i class="fas fa-search"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--t3); font-size: 0.75rem;"></i>
                            <input type="text" id="modalStudentSearch" class="modal-input" placeholder="Search student..."
                                style="padding-left: 2rem;" oninput="filterModalStudents(this.value)">
                        </div>
                        <div id="modalStudentList" class="student-selector" style="max-height: 300px; overflow-y: auto;">
                            <div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading students...</div>
                        </div>
                        <div id="modalSelectedStudentsContainer"></div>
                        <small class="modal-hint">
                            <i class="fas fa-info-circle"></i> Click on students to select/unselect them
                        </small>
                    </div>

                    {{-- Fee Structure Selection --}}
                    <div class="modal-form-group" id="modalStepFeeStructure" style="display: none;">
                        <label class="modal-label">
                            <i class="fas fa-layer-group"></i> 4. Fee Structure
                        </label>
                        <select name="fee_structure_id" required class="modal-select" id="modalFeeStructure">
                            <option value="">— Select fee structure —</option>
                            @foreach($structures as $struct)
                                <option value="{{ $struct->id }}">{{ $struct->name }} - Term {{ $struct->term }} (UGX
                                    {{ number_format($struct->total_amount, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Discount Section --}}
                    <div class="modal-form-row" id="modalStepDiscount" style="display: none;">
                        <div class="discount-card">
                            <label class="modal-label">
                                <i class="fas fa-tag"></i> Discount Amount (UGX)
                            </label>
                            <input type="text" name="discount_amount" value="0" class="modal-input">
                        </div>
                        <div class="discount-card">
                            <label class="modal-label">
                                <i class="fas fa-pen"></i> Discount Reason
                            </label>
                            <input type="text" name="discount_reason" placeholder="e.g., Scholarship, Sibling discount"
                                class="modal-input">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-fin btn-outline-fin" onclick="closeAllocateModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-fin btn-primary-fin" id="submitBulkBtn">
                        <i class="fas fa-check"></i> Allocate Fees
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Allocation Modal --}}
    <div id="editAllocationModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Fee Allocation</h3>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" id="editAllocationForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="modal-form-group">
                        <label class="modal-label">
                            <i class="fas fa-user-graduate"></i> Student
                        </label>
                        <input type="text" id="editStudentName" class="modal-input" readonly disabled
                            style="background: #f1f5f9;">
                        <input type="hidden" id="editAllocationId" name="allocation_id">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">
                            <i class="fas fa-layer-group"></i> Fee Structure
                        </label>
                        <select name="fee_structure_id" required class="modal-select" id="editFeeStructure">
                            <option value="">— Select fee structure —</option>
                            @foreach($structures as $struct)
                                <option value="{{ $struct->id }}">{{ $struct->name }} - Term {{ $struct->term }} (UGX
                                    {{ number_format($struct->total_amount, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-form-row">
                        <div class="discount-card">
                            <label class="modal-label">
                                <i class="fas fa-tag"></i> Discount Amount (UGX)
                            </label>
                            <input type="text" name="discount_amount" id="editDiscountAmount" class="modal-input" value="0">
                        </div>
                        <div class="discount-card">
                            <label class="modal-label">
                                <i class="fas fa-pen"></i> Discount Reason
                            </label>
                            <input type="text" name="discount_reason" id="editDiscountReason" class="modal-input"
                                placeholder="e.g., Scholarship, Sibling discount">
                        </div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">
                            <i class="fas fa-info-circle"></i> Current Allocated Amount
                        </label>
                        <input type="text" id="editAllocatedAmount" class="modal-input" readonly disabled
                            style="background: #f1f5f9;">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-fin btn-outline-fin" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-fin btn-primary-fin">
                        <i class="fas fa-save"></i> Update Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div>
    </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Store fee structure amounts for validation
        const feeStructureAmounts = @json($structures->mapWithKeys(fn($s) => [$s->id => $s->total_amount]));

        function applyFilters() {
            let year = document.getElementById('filterYear').value;
            let term = document.getElementById('filterTerm').value;
            let status = document.getElementById('filterStatus').value;
            let url = "{{ route('finance.fee-allocations') }}?year=" + year + "&term=" + term;
            if (status) url += "&status=" + status;
            window.location.href = url;
        }

        function openAllocateModal() {
            modalState = { classId: null, className: null, streamId: null, streamName: null };

            document.querySelectorAll('#modalClassGrid .selector-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('modalStepStream').style.display = 'none';
            document.getElementById('modalStepStudents').style.display = 'none';
            document.getElementById('modalStepFeeStructure').style.display = 'none';
            document.getElementById('modalStepDiscount').style.display = 'none';
            document.getElementById('modalStudentSearch').value = '';
            document.getElementById('modalFeeStructure').value = '';
            document.getElementById('modalSelectedStudentsContainer').innerHTML = '';

            // Reset discount amount
            const discountInput = document.querySelector('#allocateModal input[name="discount_amount"]');
            if (discountInput) {
                discountInput.value = '0';
                discountInput.setAttribute('data-raw-value', '0');
            }

            // Reset selected fee structure amount
            selectedFeeStructureAmount = 0;

            document.getElementById('allocateModal').style.display = 'flex';
        }

        function closeAllocateModal() {
            document.getElementById('allocateModal').style.display = 'none';
        }

        window.onclick = function (event) {
            let modal = document.getElementById('allocateModal');
            if (event.target == modal) closeAllocateModal();
            let editModal = document.getElementById('editAllocationModal');
            if (event.target == editModal) closeEditModal();
        }

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2f2ccb',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#2f2ccb'
            });
        @endif

        // ─────────────────────────────────────────────────────────────────
        // Modal Class/Stream/Student Filtering
        // ─────────────────────────────────────────────────────────────────
        let modalState = { classId: null, className: null, streamId: null, streamName: null };
        let modalAllStudents = [];

        function selectModalClass(card) {
            document.querySelectorAll('#modalClassGrid .selector-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            modalState.classId = card.dataset.classId;
            modalState.className = card.dataset.className;

            document.getElementById('modalSelectedClassId').value = modalState.classId;
            document.getElementById('modalChosenClassName').textContent = modalState.className;

            document.getElementById('modalStepStream').style.display = 'block';
            document.getElementById('modalStepStudents').style.display = 'none';
            document.getElementById('modalStepFeeStructure').style.display = 'none';
            document.getElementById('modalStepDiscount').style.display = 'none';

            loadModalStreams(modalState.classId);
        }

        function resetModalToClass() {
            modalState = { classId: null, className: null, streamId: null, streamName: null };
            document.querySelectorAll('#modalClassGrid .selector-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('modalStepStream').style.display = 'none';
            document.getElementById('modalStepStudents').style.display = 'none';
            document.getElementById('modalStepFeeStructure').style.display = 'none';
            document.getElementById('modalStepDiscount').style.display = 'none';
        }

        async function loadModalStreams(classId) {
            const grid = document.getElementById('modalStreamGrid');
            grid.innerHTML = '<div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading streams...</div>';

            try {
                const r = await fetch(`{{ route('finance.streams-by-class') }}?class_id=${classId}`);
                const data = await r.json();

                if (!data.length) {
                    grid.innerHTML = '<div class="loading-row"><i class="fas fa-exclamation-circle"></i> No streams found.</div>';
                    return;
                }

                grid.innerHTML = data.map(s => `
                                        <div class="selector-card" data-stream-id="${s.stream_id}" data-stream-name="${s.stream_name || s.stream_id}" onclick="selectModalStream(this)">
                                            <div class="sc-icon">📚</div>
                                            <div class="sc-label">${s.stream_name || s.stream_id}</div>
                                        </div>
                                    `).join('');
            } catch (e) {
                grid.innerHTML = '<div class="loading-row" style="color: var(--r);"><i class="fas fa-times-circle"></i> Failed to load streams.</div>';
            }
        }

        function selectModalStream(card) {
            document.querySelectorAll('#modalStreamGrid .selector-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            modalState.streamId = card.dataset.streamId;
            modalState.streamName = card.dataset.streamName;

            document.getElementById('modalSelectedStreamId').value = modalState.streamId;
            document.getElementById('modalChosenStreamName').textContent = modalState.streamName;

            document.getElementById('modalStepStudents').style.display = 'block';
            document.getElementById('modalStepFeeStructure').style.display = 'block';
            document.getElementById('modalStepDiscount').style.display = 'block';

            loadModalStudents(modalState.classId, modalState.streamId);
        }

        function resetModalToStream() {
            modalState.streamId = null;
            modalState.streamName = null;
            document.querySelectorAll('#modalStreamGrid .selector-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('modalStepStudents').style.display = 'none';
            document.getElementById('modalStepFeeStructure').style.display = 'none';
            document.getElementById('modalStepDiscount').style.display = 'none';
        }

        async function loadModalStudents(classId, streamId) {
            const list = document.getElementById('modalStudentList');
            list.innerHTML = '<div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading students...</div>';

            try {
                const r = await fetch(`{{ route('finance.students-by-stream') }}?class_id=${classId}&stream_id=${streamId}`);
                modalAllStudents = await r.json();
                renderModalStudents(modalAllStudents);
            } catch (e) {
                list.innerHTML = '<div class="loading-row" style="color: var(--r);"><i class="fas fa-times-circle"></i> Failed to load students.</div>';
            }
        }

        function renderModalStudents(students) {
            const list = document.getElementById('modalStudentList');
            if (!students.length) {
                list.innerHTML = '<div class="loading-row"><i class="fas fa-user-slash"></i> No students found.</div>';
                return;
            }

            list.innerHTML = students.map(s => `
                                    <div class="student-chip" data-id="${s.id}" data-name="${s.firstname} ${s.lastname}" data-adm="${s.admission_number ?? 'N/A'}" onclick="toggleModalStudent(this)">
                                        <i class="fas fa-user-graduate"></i>
                                        <div class="student-info">
                                            <div class="student-name">${s.firstname} ${s.lastname}</div>
                                            <div class="student-adm">ADM: ${s.admission_number ?? 'N/A'} · ${s.gender ?? ''}</div>
                                        </div>
                                    </div>
                                `).join('');
        }

        function toggleModalStudent(element) {
            element.classList.toggle('selected');
            const container = document.getElementById('modalSelectedStudentsContainer');
            const studentId = element.dataset.id;

            if (element.classList.contains('selected')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = studentId;
                input.setAttribute('data-student-id', studentId);
                container.appendChild(input);
            } else {
                const inputs = container.querySelectorAll(`input[data-student-id="${studentId}"]`);
                inputs.forEach(input => input.remove());
            }
        }

        function filterModalStudents(query) {
            const term = query.toLowerCase();
            const filtered = modalAllStudents.filter(s =>
                (s.firstname + ' ' + s.lastname).toLowerCase().includes(term) ||
                (s.admission_number ?? '').toLowerCase().includes(term)
            );

            const list = document.getElementById('modalStudentList');
            if (!filtered.length) {
                list.innerHTML = '<div class="loading-row"><i class="fas fa-user-slash"></i> No matching students found.</div>';
                return;
            }

            list.innerHTML = filtered.map(s => `
                                    <div class="student-chip" data-id="${s.id}" data-name="${s.firstname} ${s.lastname}" data-adm="${s.admission_number ?? 'N/A'}" onclick="toggleModalStudent(this)">
                                        <i class="fas fa-user-graduate"></i>
                                        <div class="student-info">
                                            <div class="student-name">${s.firstname} ${s.lastname}</div>
                                            <div class="student-adm">ADM: ${s.admission_number ?? 'N/A'} · ${s.gender ?? ''}</div>
                                        </div>
                                    </div>
                                `).join('');
        }

        // ─────────────────────────────────────────────────────────────────
        // Format Number with commas
        // ─────────────────────────────────────────────────────────────────
        function formatNumberWithCommas(value) {
            let numbers = value.toString().replace(/\D/g, '');
            if (numbers === '' || numbers === '0') return '0';
            return parseInt(numbers, 10).toLocaleString('en-US');
        }

        function parseFormattedNumber(value) {
            return value.toString().replace(/,/g, '');
        }

        // ─────────────────────────────────────────────────────────────────
        // CREATE MODAL - Discount Validation
        // ─────────────────────────────────────────────────────────────────
        const modalFeeStructure = document.getElementById('modalFeeStructure');
        const modalDiscountInput = document.querySelector('#allocateModal input[name="discount_amount"]');
        let selectedFeeStructureAmount = 0;

        function validateDiscountAmount(discountValue, structureAmount) {
            const discount = parseInt(discountValue, 10) || 0;
            if (isNaN(discount)) return { valid: true };

            if (discount > structureAmount && structureAmount > 0) {
                return {
                    valid: false,
                    message: `Discount cannot exceed the fee structure amount (UGX ${formatNumberWithCommas(structureAmount)})`
                };
            }
            return { valid: true };
        }

        if (modalFeeStructure) {
            modalFeeStructure.addEventListener('change', function () {
                const structureId = this.value;
                selectedFeeStructureAmount = feeStructureAmounts[structureId] || 0;

                if (modalDiscountInput && selectedFeeStructureAmount > 0) {
                    const rawDiscount = parseInt(parseFormattedNumber(modalDiscountInput.value), 10) || 0;
                    const validation = validateDiscountAmount(rawDiscount, selectedFeeStructureAmount);

                    if (!validation.valid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Discount',
                            text: validation.message,
                            confirmButtonColor: '#2f2ccb'
                        });
                        modalDiscountInput.value = formatNumberWithCommas(selectedFeeStructureAmount);
                        modalDiscountInput.setAttribute('data-raw-value', selectedFeeStructureAmount);
                    }
                }
            });
        }

        if (modalDiscountInput) {
            // Format initial value
            if (modalDiscountInput.value && modalDiscountInput.value !== '0') {
                let rawValue = modalDiscountInput.value;
                if (!isNaN(rawValue) && rawValue.indexOf(',') === -1) {
                    modalDiscountInput.value = formatNumberWithCommas(rawValue);
                }
            }
            modalDiscountInput.setAttribute('data-raw-value', parseFormattedNumber(modalDiscountInput.value));

            // Format as user types
            modalDiscountInput.addEventListener('input', function (e) {
                const input = e.target;
                const rawValue = input.value;
                const numericValue = parseFormattedNumber(rawValue);

                input.setAttribute('data-raw-value', numericValue);

                if (numericValue !== '') {
                    input.value = formatNumberWithCommas(numericValue);
                } else {
                    input.value = '';
                }

                const newLength = input.value.length;
                input.setSelectionRange(newLength, newLength);
            });

            // Validate on blur
            modalDiscountInput.addEventListener('blur', function () {
                const rawDiscount = parseInt(parseFormattedNumber(this.value), 10) || 0;
                const validation = validateDiscountAmount(rawDiscount, selectedFeeStructureAmount);

                if (!validation.valid && selectedFeeStructureAmount > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Discount',
                        text: validation.message,
                        confirmButtonColor: '#2f2ccb'
                    });
                    this.value = formatNumberWithCommas(selectedFeeStructureAmount);
                    this.setAttribute('data-raw-value', selectedFeeStructureAmount);
                }
            });
        }

        // ─────────────────────────────────────────────────────────────────
        // CREATE MODAL - Form Submission
        // ─────────────────────────────────────────────────────────────────
        const bulkForm = document.getElementById('bulkAllocationForm');

        bulkForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate discount amount before submission
            if (modalDiscountInput && selectedFeeStructureAmount > 0) {
                const rawDiscount = parseInt(parseFormattedNumber(modalDiscountInput.value), 10) || 0;
                const validation = validateDiscountAmount(rawDiscount, selectedFeeStructureAmount);

                if (!validation.valid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Discount',
                        text: validation.message,
                        confirmButtonColor: '#2f2ccb'
                    });
                    return;
                }
            }

            // Convert discount amount back to raw number
            if (modalDiscountInput) {
                const rawValue = parseFormattedNumber(modalDiscountInput.value);
                modalDiscountInput.value = rawValue;
            }

            const selectedStudents = document.querySelectorAll('#modalSelectedStudentsContainer input[name="student_ids[]"]');
            if (selectedStudents.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Students Selected',
                    text: 'Please select at least one student to allocate fees.',
                    confirmButtonColor: '#2f2ccb'
                });
                return;
            }

            const feeStructure = document.getElementById('modalFeeStructure').value;
            if (!feeStructure) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Fee Structure Selected',
                    text: 'Please select a fee structure to allocate.',
                    confirmButtonColor: '#2f2ccb'
                });
                return;
            }

            const studentCount = selectedStudents.length;
            const discountAmount = modalDiscountInput ? modalDiscountInput.value : '0';

            Swal.fire({
                title: 'Allocate Fees?',
                html: `<span style="color: #475569;">You are about to allocate fees to <strong>${studentCount}</strong> student(s).<br>Discount: UGX ${parseInt(discountAmount).toLocaleString()}<br>This action cannot be undone.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Yes, allocate!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Allocating fees to selected students...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    bulkForm.submit();
                } else {
                    // Restore formatted value if cancelled
                    if (modalDiscountInput && modalDiscountInput.getAttribute('data-raw-value')) {
                        const rawValue = modalDiscountInput.getAttribute('data-raw-value');
                        if (rawValue && rawValue !== '') {
                            modalDiscountInput.value = formatNumberWithCommas(rawValue);
                        }
                    }
                }
            });
        });

        // ─────────────────────────────────────────────────────────────────
        // EDIT MODAL - Functions
        // ─────────────────────────────────────────────────────────────────
        const editDiscountInput = document.getElementById('editDiscountAmount');
        const editFeeStructure = document.getElementById('editFeeStructure');
        let editSelectedFeeStructureAmount = 0;

        function validateEditDiscountAmount(discountValue, structureAmount) {
            const discount = parseInt(discountValue, 10) || 0;
            if (isNaN(discount)) return { valid: true };

            if (discount > structureAmount && structureAmount > 0) {
                return {
                    valid: false,
                    message: `Discount cannot exceed the fee structure amount (UGX ${formatNumberWithCommas(structureAmount)})`
                };
            }
            return { valid: true };
        }

        function editAllocation(id) {
            fetch(`{{ url('finance/fee-allocation') }}/${id}/data`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editAllocationId').value = data.id;
                    document.getElementById('editStudentName').value = data.student_name + ' (ADM: ' + data.student_adm + ')';
                    document.getElementById('editFeeStructure').value = data.fee_structure_id;

                    // Display allocated amount correctly
                    const allocatedAmount = parseInt(data.allocated_amount, 10) || 0;
                    document.getElementById('editAllocatedAmount').value = 'UGX ' + allocatedAmount.toLocaleString('en-US');

                    // Set the selected fee structure amount for validation
                    editSelectedFeeStructureAmount = feeStructureAmounts[data.fee_structure_id] || allocatedAmount;

                    // Format discount amount correctly - ensure it's parsed as integer
                    if (editDiscountInput) {
                        let existingDiscount = parseInt(data.discount_amount, 10) || 0;
                        existingDiscount = Math.floor(existingDiscount);
                        editDiscountInput.value = formatNumberWithCommas(existingDiscount);
                        editDiscountInput.setAttribute('data-raw-value', existingDiscount.toString());
                    }
                    document.getElementById('editDiscountReason').value = data.discount_reason || '';

                    document.getElementById('editAllocationModal').style.display = 'flex';
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load allocation data.',
                        confirmButtonColor: '#2f2ccb'
                    });
                });
        }

        function closeEditModal() {
            document.getElementById('editAllocationModal').style.display = 'none';
        }

        // Edit modal - Fee structure change validation (DO NOT auto-set discount)
        if (editFeeStructure) {
            editFeeStructure.addEventListener('change', function () {
                const structureId = this.value;
                editSelectedFeeStructureAmount = feeStructureAmounts[structureId] || 0;

                // Only validate, DO NOT change the discount value
                if (editDiscountInput && editSelectedFeeStructureAmount > 0) {
                    const rawDiscount = parseInt(parseFormattedNumber(editDiscountInput.value), 10) || 0;
                    const validation = validateEditDiscountAmount(rawDiscount, editSelectedFeeStructureAmount);

                    if (!validation.valid) {
                        // Show warning but don't auto-change the discount
                        Swal.fire({
                            icon: 'warning',
                            title: 'Discount Exceeds Structure Amount',
                            html: `The current discount (UGX ${editDiscountInput.value}) exceeds the new fee structure amount (UGX ${formatNumberWithCommas(editSelectedFeeStructureAmount)}).<br><br>Please adjust the discount amount.`,
                            confirmButtonColor: '#2f2ccb'
                        });
                    }
                }
            });
        }

        // Edit modal - Format discount input
        if (editDiscountInput) {
            editDiscountInput.addEventListener('input', function (e) {
                const input = e.target;
                const rawValue = input.value;
                const numericValue = parseFormattedNumber(rawValue);

                input.setAttribute('data-raw-value', numericValue);

                if (numericValue !== '') {
                    input.value = formatNumberWithCommas(numericValue);
                } else {
                    input.value = '';
                }

                const newLength = input.value.length;
                input.setSelectionRange(newLength, newLength);
            });

            // Validate on blur - show warning if discount exceeds new structure
            editDiscountInput.addEventListener('blur', function () {
                const rawDiscount = parseInt(parseFormattedNumber(this.value), 10) || 0;
                const validation = validateEditDiscountAmount(rawDiscount, editSelectedFeeStructureAmount);

                if (!validation.valid && editSelectedFeeStructureAmount > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Discount',
                        text: validation.message,
                        confirmButtonColor: '#2f2ccb'
                    });
                }
            });
        }

        // Submit edit form
        const editForm = document.getElementById('editAllocationForm');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const allocationId = document.getElementById('editAllocationId').value;

                // Validate discount amount against selected fee structure
                if (editDiscountInput && editSelectedFeeStructureAmount > 0) {
                    const rawDiscount = parseInt(parseFormattedNumber(editDiscountInput.value), 10) || 0;
                    const validation = validateEditDiscountAmount(rawDiscount, editSelectedFeeStructureAmount);

                    if (!validation.valid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Discount',
                            text: validation.message,
                            confirmButtonColor: '#2f2ccb'
                        });
                        return;
                    }
                }

                const discountAmount = editDiscountInput ? parseInt(parseFormattedNumber(editDiscountInput.value), 10) || 0 : 0;

                Swal.fire({
                    title: 'Update Allocation?',
                    text: 'Are you sure you want to update this fee allocation?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2f2ccb',
                    cancelButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, update!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Updating fee allocation...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(`{{ url('finance/fee-allocation') }}/${allocationId}`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                fee_structure_id: editFeeStructure.value,
                                discount_amount: discountAmount,
                                discount_reason: document.getElementById('editDiscountReason').value
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated!',
                                        text: data.message,
                                        confirmButtonColor: '#2f2ccb',
                                        timer: 2000
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: data.message,
                                        confirmButtonColor: '#2f2ccb'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to update allocation.',
                                    confirmButtonColor: '#2f2ccb'
                                });
                            });
                    }
                });
            });
        }

        // ─────────────────────────────────────────────────────────────────
        // Delete Allocation Function
        // ─────────────────────────────────────────────────────────────────
        function deleteAllocation(id) {
            Swal.fire({
                title: 'Delete Allocation?',
                html: `<span style="color: #475569;">This action cannot be undone. Any linked payments will be affected.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Deleting fee allocation...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`{{ url('finance/fee-allocation') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    confirmButtonColor: '#2f2ccb',
                                    timer: 2000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                    confirmButtonColor: '#2f2ccb'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to delete allocation.',
                                confirmButtonColor: '#2f2ccb'
                            });
                        });
                }
            });
        }

        // Scroll indicator for table
        const tableWrapper = document.querySelector('.table-wrapper');
        if (tableWrapper) {
            function updateScrollIndicator() {
                const scrollLeft = tableWrapper.scrollLeft;
                const maxScrollLeft = tableWrapper.scrollWidth - tableWrapper.clientWidth;
                const scrollPercent = (scrollLeft / maxScrollLeft) * 100;
                tableWrapper.style.setProperty('--scroll-percent', scrollPercent + '%');
            }

            tableWrapper.addEventListener('scroll', updateScrollIndicator);
            window.addEventListener('resize', updateScrollIndicator);
            setTimeout(updateScrollIndicator, 100);
        }

    </script>
@endsection