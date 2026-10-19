<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .rc-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .rc-hero .hero-badge {
            background: rgba(44, 41, 202, .25);
            border: 1px solid rgba(107, 105, 232, .5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .rc-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            margin: .25rem 0;
        }

        .rc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .68);
            font-size: .85rem;
            max-width: 780px;
        }

        .rc-panel {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            margin-bottom: 1.4rem;
            overflow: hidden;
        }

        .rc-panel .ph {
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 800;
            font-size: .95rem;
            color: #1a1a3a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
        }

        .rc-panel .pb {
            padding: 1.3rem 1.5rem;
        }

        .cs-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .cs-chip {
            border: 2px solid #e4e6ff;
            border-radius: .8rem;
            padding: .45rem .8rem;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
            color: #4a4a6a;
            background: #fafbff;
            transition: all .15s ease;
            position: relative;
        }

        .cs-chip:hover {
            border-color: #c7c5ff;
            background: #f4f5ff;
        }

        .cs-chip.on {
            border-color: #2C29CA;
            background: #eef0ff;
            color: #2C29CA;
            box-shadow: 0 2px 8px rgba(44, 41, 202, .15);
        }

        .cs-chip.on::after {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: .6rem;
            position: absolute;
            top: -6px;
            right: -6px;
            background: #2C29CA;
            color: #fff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        }

        .comp {
            border: 2px solid #e8eaff;
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
            background: #fcfcff;
        }

        .comp.is-exam {
            border-left: 5px solid #f59e0b;
        }

        .comp.is-assess {
            border-left: 5px solid #2C29CA;
        }

        .comp .tag {
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .comp .tag.assess {
            color: #2C29CA;
        }

        .comp .tag.exam {
            color: #b45309;
        }

        .pick-box {
            max-height: 340px;
            overflow: auto;
            border: 1px solid #e8eaff;
            border-radius: .8rem;
            padding: .6rem .8rem;
            background: #fff;
        }

        .total-pill {
            font-weight: 800;
            border-radius: 999px;
            padding: .3rem .9rem;
            font-size: .8rem;
            background: #eef0ff;
            color: #2C29CA;
        }

        .total-pill.off {
            background: #fff4e0;
            color: #b45309;
        }

        .hint {
            font-size: .75rem;
            color: #7a7a9a;
        }

        /* ── Toggle switch styling ── */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .5rem .6rem;
            border-radius: .55rem;
            margin-left: 1rem;
            transition: background .15s ease;
            cursor: pointer;
            margin-bottom: 0;
        }

        .toggle-row:hover {
            background: #f4f5ff;
        }

        .toggle-row .toggle-info {
            flex: 1;
            min-width: 0;
        }

        .toggle-row .toggle-title {
            font-size: .8rem;
            font-weight: 600;
            color: #2a2a4a;
            line-height: 1.25;
        }

        .toggle-row .toggle-meta {
            font-size: .7rem;
            color: #8b8baa;
            line-height: 1.25;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 21px;
            flex-shrink: 0;
            cursor: pointer;
            margin-bottom: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .toggle-switch .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #d6d8f0;
            border-radius: 999px;
            transition: background-color .2s ease;
        }

        .toggle-switch .slider::before {
            content: "";
            position: absolute;
            height: 15px;
            width: 15px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            border-radius: 50%;
            transition: transform .2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        }

        .toggle-switch input:checked+.slider {
            background-color: #2C29CA;
        }

        .toggle-switch input:checked+.slider::before {
            transform: translateX(17px);
        }

        .toggle-switch input:focus-visible+.slider {
            outline: 2px solid #6b69e8;
            outline-offset: 2px;
        }

        .toggle-switch.sm {
            width: 32px;
            height: 18px;
        }

        .toggle-switch.sm .slider::before {
            height: 12px;
            width: 12px;
            left: 3px;
            bottom: 3px;
        }

        .toggle-switch.sm input:checked+.slider::before {
            transform: translateX(14px);
        }

        /* ── Collapsible exam groups ── */
        .exam-group {
            margin-top: .65rem;
            border: 1px solid #e8eaff;
            border-radius: .7rem;
            overflow: hidden;
            background: #fff;
        }

        .exam-group:first-child {
            margin-top: 0;
        }

        .exam-group.active {
            border-color: #c7c5ff;
            background: #fafbff;
        }

        .exam-head {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .7rem;
            cursor: pointer;
            user-select: none;
            background: #f6f7ff;
            transition: background .15s ease;
            margin: 0;
        }

        .exam-head:hover {
            background: #eef0ff;
        }

        .exam-group.active .exam-head {
            background: #eef0ff;
        }

        .exam-head .caret {
            font-size: .7rem;
            color: #6b69e8;
            width: 14px;
            text-align: center;
            transition: transform .2s ease;
            flex-shrink: 0;
        }

        .exam-group.open .exam-head .caret {
            transform: rotate(90deg);
        }

        .exam-head .exam-name {
            font-weight: 800;
            font-size: .8rem;
            color: #1a1a3a;
            line-height: 1.2;
        }

        .exam-group.active .exam-head .exam-name {
            color: #2C29CA;
        }

        .exam-head .exam-meta {
            font-size: .68rem;
            color: #8b8baa;
            font-weight: 600;
            line-height: 1.2;
        }

        .exam-head .exam-count {
            margin-left: auto;
            font-size: .65rem;
            font-weight: 800;
            padding: .12rem .5rem;
            border-radius: 999px;
            background: #e4e6ff;
            color: #6b6b8d;
            flex-shrink: 0;
        }

        .exam-group.active .exam-head .exam-count {
            background: #2C29CA;
            color: #fff;
        }

        .exam-body {
            padding: .5rem .6rem .6rem;
            display: none;
        }

        .exam-group.open .exam-body {
            display: block;
        }

        .pick-subject-head {
            font-weight: 700;
            font-size: .72rem;
            color: #5a5a80;
            margin: .6rem 0 .15rem 1.6rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .pick-subject-head:first-child {
            margin-top: 0;
        }

        /* ── Component header (per assessment / exam part) ── */
        .comp-head {
            margin-bottom: .9rem;
        }

        .comp-head-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            padding-bottom: .75rem;
            margin-bottom: .85rem;
            border-bottom: 1px dashed #e4e6ff;
        }

        .comp-head-title {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            min-width: 0;
        }

        .comp-head-icon {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--accent) 28%, #fff);
        }

        /* Fallback for browsers without color-mix */
        @supports not (background: color-mix(in srgb, red 10%, white)) {
            .comp-head-icon {
                background: #f4f5ff;
                border-color: #d6d8f0;
            }
        }

        .comp-head-label {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .comp-head-sub {
            font-size: .72rem;
            color: #7a7a9a;
            line-height: 1.35;
            margin-top: .1rem;
            max-width: 520px;
        }

        .comp-remove {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid transparent;
            background: transparent;
            color: #b23b3b;
            font-size: .72rem;
            font-weight: 700;
            padding: .3rem .55rem;
            border-radius: .45rem;
            cursor: pointer;
            transition: all .15s ease;
            flex-shrink: 0;
        }

        .comp-remove:hover {
            background: #fff0f0;
            border-color: #f3c9c9;
            color: #a02020;
        }

        .comp-head-fields {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .comp-field {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .comp-field-label {
            flex: 1 1 260px;
            min-width: 0;
        }

        .comp-field-weight {
            flex: 0 0 140px;
        }

        .comp-field>label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b6b8d;
            margin: 0;
        }

        .comp-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .comp-input-icon {
            position: absolute;
            left: .65rem;
            font-size: .72rem;
            color: #a5a5c4;
            pointer-events: none;
        }

        .comp-input-wrap input {
            padding-left: 1.9rem !important;
            border-radius: .55rem !important;
            border: 1px solid #dfe2f5 !important;
            background: #fbfbff;
            font-size: .82rem;
            height: auto;
            padding-top: .5rem;
            padding-bottom: .5rem;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .comp-input-wrap input:focus {
            border-color: #6b69e8 !important;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(107, 105, 232, .12);
            outline: none;
        }

        .comp-input-suffix {
            position: absolute;
            right: .65rem;
            font-size: .66rem;
            font-weight: 800;
            color: #8b8baa;
            text-transform: uppercase;
            letter-spacing: .04em;
            pointer-events: none;
        }

        .comp-field-weight input {
            padding-right: 2.4rem !important;
        }

        /* ── Component header (per assessment / exam part) ── */
        .comp-head {
            margin-bottom: .9rem;
        }

        .comp-head-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            padding-bottom: .75rem;
            margin-bottom: .85rem;
            border-bottom: 1px dashed #e4e6ff;
        }

        .comp-head-title {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            min-width: 0;
        }

        .comp-head-icon {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--accent) 28%, #fff);
        }

        @supports not (background: color-mix(in srgb, red 10%, white)) {
            .comp-head-icon {
                background: #f4f5ff;
                border-color: #d6d8f0;
            }
        }

        .comp-head-label {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .comp-head-sub {
            font-size: .72rem;
            color: #7a7a9a;
            line-height: 1.35;
            margin-top: .1rem;
            max-width: 520px;
        }

        /* Remove button — styled in its "hover" state by default */
        .comp-remove {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid #f3c9c9;
            background: #fff0f0;
            color: #a02020;
            font-size: .72rem;
            font-weight: 700;
            padding: .3rem .55rem;
            border-radius: .45rem;
            cursor: pointer;
            transition: all .15s ease;
            flex-shrink: 0;
        }

        .comp-remove:hover {
            background: #ffe3e3;
            border-color: #e8b3b3;
            color: #8a1a1a;
        }

        .comp-head-fields {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .comp-field {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .comp-field-label {
            flex: 1 1 260px;
            min-width: 0;
        }

        .comp-field-weight {
            flex: 0 0 140px;
        }

        .comp-field>label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b6b8d;
            margin: 0;
        }

        .comp-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .comp-input-icon {
            position: absolute;
            left: .7rem;
            font-size: .78rem;
            color: #2C29CA;
            opacity: .7;
            pointer-events: none;
            z-index: 1;
        }

        /* Visible input field */
        .comp-input-wrap input {
            padding-left: 2.1rem !important;
            border-radius: .55rem !important;
            border: 1.5px solid #c9cdf0 !important;
            background: #f3f4ff !important;
            color: #1a1a3a;
            font-size: .85rem;
            font-weight: 600;
            height: auto;
            padding-top: .55rem;
            padding-bottom: .55rem;
            box-shadow: inset 0 1px 2px rgba(44, 41, 202, .05);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .comp-input-wrap input::placeholder {
            color: #9496b8;
            font-weight: 500;
        }

        .comp-input-wrap input:hover {
            border-color: #a8adde !important;
            background: #eef0ff !important;
        }

        .comp-input-wrap input:focus {
            border-color: #2C29CA !important;
            background: #fff !important;
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .15);
            outline: none;
        }

        .comp-input-suffix {
            position: absolute;
            right: .7rem;
            font-size: .66rem;
            font-weight: 800;
            color: #2C29CA;
            opacity: .75;
            text-transform: uppercase;
            letter-spacing: .04em;
            pointer-events: none;
        }

        .comp-field-weight input {
            padding-right: 2.5rem !important;
        }

        /* ── Panel action buttons (Add assessments / Add examination) ── */
        .rc-actions {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .rc-action-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .85rem .45rem .5rem;
            border-radius: .6rem;
            border: 1.5px solid transparent;
            font-size: .78rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .rc-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: .4rem;
            font-size: .72rem;
            flex-shrink: 0;
        }

        .rc-action-text {
            letter-spacing: .01em;
        }

        /* Assessments button — indigo */
        .rc-action-assess {
            background: #eef0ff;
            border-color: #c7c5ff;
            color: #2C29CA;
        }

        .rc-action-assess .rc-action-icon {
            background: #2C29CA;
            color: #fff;
        }

        .rc-action-assess:hover {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
            box-shadow: 0 4px 14px rgba(44, 41, 202, .25);
            transform: translateY(-1px);
        }

        .rc-action-assess:hover .rc-action-icon {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        /* Examination button — amber */
        .rc-action-exam {
            background: #fff4e0;
            border-color: #fbd9a0;
            color: #b45309;
        }

        .rc-action-exam .rc-action-icon {
            background: #f59e0b;
            color: #fff;
        }

        .rc-action-exam:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #fff;
            box-shadow: 0 4px 14px rgba(245, 158, 11, .3);
            transform: translateY(-1px);
        }

        .rc-action-exam:hover .rc-action-icon {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        /* Small screens: keep buttons compact */
        @media (max-width: 575.98px) {
            .rc-action-btn {
                padding: .4rem .65rem .4rem .4rem;
                font-size: .74rem;
            }

            .rc-action-icon {
                width: 22px;
                height: 22px;
                font-size: .65rem;
            }
        }

        /* ── Report-card details: standardized inputs ── */
        .rc-label {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #5a5a80;
            margin-bottom: .4rem;
        }

        .rc-label-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: .4rem;
            background: #eef0ff;
            color: #2C29CA;
            font-size: .6rem;
            flex-shrink: 0;
        }

        .rc-label .text-danger {
            font-size: .9em;
            margin-left: -2px;
        }

        .rc-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .rc-input-icon {
            position: absolute;
            left: .7rem;
            font-size: .78rem;
            color: #2C29CA;
            opacity: .7;
            pointer-events: none;
            z-index: 1;
        }

        .rc-input-wrap .form-control {
            padding-left: 2.1rem !important;
            border-radius: .55rem !important;
            border: 1.5px solid #c9cdf0 !important;
            background: #f3f4ff !important;
            color: #1a1a3a;
            font-size: .85rem;
            font-weight: 600;
            height: auto;
            padding-top: .55rem;
            padding-bottom: .55rem;
            box-shadow: inset 0 1px 2px rgba(44, 41, 202, .05);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .rc-input-wrap .form-control::placeholder {
            color: #9496b8;
            font-weight: 500;
        }

        .rc-input-wrap .form-control:hover {
            border-color: #a8adde !important;
            background: #eef0ff !important;
        }

        .rc-input-wrap .form-control:focus {
            border-color: #2C29CA !important;
            background: #fff !important;
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .15);
            outline: none;
        }

        /* select arrow spacing */
        .rc-input-wrap select.form-control {
            padding-right: 2rem !important;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%232C29CA' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right .65rem center !important;
            background-size: 12px !important;
            background-color: #f3f4ff !important;
        }

        .rc-input-wrap select.form-control:hover {
            background-color: #eef0ff !important;
        }

        .rc-input-wrap select.form-control:focus {
            background-color: #fff !important;
        }

        /* ── Footer action buttons (Save / Cancel) ── */
        .rc-footer-actions {
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
            padding-top: .5rem;
        }

        .rc-btn {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .6rem 1.15rem .6rem .6rem;
            border-radius: .65rem;
            border: 1.5px solid transparent;
            font-size: .85rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .rc-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: .45rem;
            font-size: .78rem;
            flex-shrink: 0;
        }

        /* Primary — Save / Create */
        .rc-btn-primary {
            background: linear-gradient(135deg, #2C29CA 0%, #1e1b8a 100%);
            border-color: #2C29CA;
            color: #fff;
            box-shadow: 0 4px 14px rgba(44, 41, 202, .28);
        }

        .rc-btn-primary .rc-btn-icon {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .rc-btn-primary:hover {
            background: linear-gradient(135deg, #3532d8 0%, #2623a0 100%);
            border-color: #3532d8;
            color: #fff;
            box-shadow: 0 6px 18px rgba(44, 41, 202, .38);
            transform: translateY(-1px);
        }

        .rc-btn-primary:hover .rc-btn-icon {
            background: rgba(255, 255, 255, .28);
        }

        .rc-btn-primary:disabled {
            opacity: .65;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(44, 41, 202, .15);
        }

        /* Cancel — soft neutral with red accent */
        .rc-btn-cancel {
            background: #fff5f5;
            border-color: #f3c9c9;
            color: #a02020;
        }

        .rc-btn-cancel .rc-btn-icon {
            background: #ffe3e3;
            color: #a02020;
        }

        .rc-btn-cancel:hover {
            background: #a02020;
            border-color: #a02020;
            color: #fff;
            box-shadow: 0 4px 14px rgba(160, 32, 32, .25);
            transform: translateY(-1px);
        }

        .rc-btn-cancel:hover .rc-btn-icon {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .rc-btn {
                padding: .55rem .9rem .55rem .55rem;
                font-size: .8rem;
            }

            .rc-btn-icon {
                width: 24px;
                height: 24px;
                font-size: .7rem;
            }
        }

        /* Hero layout: title block + right-aligned action on large screens */
        .rc-hero .hero-content {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1.25rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .rc-hero .hero-main {
            flex: 1 1 480px;
            min-width: 0;
        }

        /* Secondary CTA — Back to O-Level Dashboard */
        .rc-hero .hero-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.1rem .55rem .55rem;
            background: rgba(255, 255, 255, .12);
            border: 1.5px solid rgba(255, 255, 255, .35);
            color: #fff;
            border-radius: .65rem;
            font-weight: 700;
            font-size: .85rem;
            text-decoration: none;
            transition: all .15s ease;
            backdrop-filter: blur(6px);
            flex-shrink: 0;
            align-self: flex-end;
            margin-bottom: .15rem;
        }

        .rc-hero .hero-cta-secondary:hover {
            background: rgba(255, 255, 255, .22);
            border-color: rgba(255, 255, 255, .55);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .22);
        }

        .rc-hero .hero-cta-secondary .hero-cta-icon {
            width: 26px;
            height: 26px;
            border-radius: .4rem;
            background: rgba(255, 255, 255, .2);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
        }

        /* Small screens: stack and make the dashboard button full-width */
        @media (max-width: 767.98px) {
            .rc-hero .hero-content {
                flex-direction: column;
                align-items: stretch;
            }

            .rc-hero .hero-cta-secondary {
                width: 100%;
                justify-content: center;
                align-self: stretch;
                margin-top: .75rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $isEdit = (bool) $card;
        $activeTermText = \App\Support\Term::activeText();
    @endphp
    <div class="container-fluid py-3">
        <div class="rc-hero">
            <span class="hero-badge"><i class="fas fa-graduation-cap me-1"></i> Secondary · Senior 1–4</span>

            <div class="hero-content">
                <div class="hero-main">
                    <div class="hero-title">{{ $isEdit ? 'Edit' : 'New' }} O-Level Report Card</div>
                    <div class="hero-subtitle">
                        Add one or more parts to the card. <strong>Assessments</strong> are averaged on the 0–3 competency
                        scale and
                        shown out of the weight you give them;
                        an <strong>Examination</strong> is shown out of its own weight. Together they form the card (for
                        example 20
                        + 80 = 100).
                    </div>
                </div>

                <a href="{{ url('/examinations/olevel-report-cards') }}" class="hero-cta-secondary">
                    <span class="hero-cta-icon"><i class="fas fa-arrow-left"></i></span>
                    <span>O Level Dashboard</span>
                </a>
            </div>
        </div>

        <div id="alertBox"></div>

        <div class="rc-panel">
            <div class="ph"><span><i class="fas fa-info-circle me-1 text-primary"></i> Report card details</span></div>
            <div class="pb">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold rc-label">
                            <span class="rc-label-icon"><i class="fas fa-tag"></i></span>
                            Name <span class="text-danger">*</span>
                        </label>
                        <div class="rc-input-wrap">
                            <i class="fas fa-pen rc-input-icon"></i>
                            <input type="text" id="examName" class="form-control" maxlength="255"
                                placeholder="e.g. Term 2 Report Card" value="{{ $card->exam_name ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold rc-label">
                            <span class="rc-label-icon"><i class="fas fa-calendar-alt"></i></span>
                            Term <span class="text-danger">*</span>
                        </label>
                        <div class="rc-input-wrap">
                            <i class="fas fa-list-ol rc-input-icon"></i>
                            <select id="term" class="form-control">
                                @foreach(['Term 1' => 'Term I', 'Term 2' => 'Term II', 'Term 3' => 'Term III'] as $v => $l)
                                    <option value="{{ $v }}" {{ ($card->term ?? $activeTermText) === $v ? 'selected' : '' }}>
                                        {{ $l }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold rc-label">
                            <span class="rc-label-icon"><i class="fas fa-calendar-check"></i></span>
                            Year <span class="text-danger">*</span>
                        </label>
                        <div class="rc-input-wrap">
                            <i class="fas fa-hashtag rc-input-icon"></i>
                            <input type="number" id="academicYear" class="form-control" min="2000" max="2099"
                                value="{{ $card->academic_year ?? Helper::active_year() }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold rc-label">
                            <span class="rc-label-icon"><i class="fas fa-award"></i></span>
                            Grading scheme <span class="text-danger">*</span>
                        </label>
                        <div class="rc-input-wrap">
                            <i class="fas fa-medal rc-input-icon"></i>
                            <select id="gradingScheme" class="form-control">
                                @foreach($gradingSchemes as $s)
                                    <option value="{{ $s->id }}" {{ ($card->grading_scheme_id ?? null) == $s->id || (!$isEdit && $s->is_default) ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold rc-label">
                            <span class="rc-label-icon"><i class="fas fa-sticky-note"></i></span>
                            Notes <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <div class="rc-input-wrap">
                            <i class="fas fa-align-left rc-input-icon"></i>
                            <input type="text" id="description" class="form-control" value="{{ $card->description ?? '' }}"
                                placeholder="Any extra notes for this report card">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rc-panel">
            <div class="ph">
                <span><i class="fas fa-users me-1 text-primary"></i> Classes</span>
                <span class="hint">Only Senior 1–4 streams are listed.</span>
            </div>
            <div class="pb">
                <div class="cs-chips" id="csChips">
                    @forelse($classStreams as $cs)
                        @php $val = $cs->class_id . '_' . $cs->stream_id; @endphp
                        <div class="cs-chip {{ in_array($val, $selectedClassStreams, true) ? 'on' : '' }}"
                            data-value="{{ $val }}">
                            {{ Helper::recordMdname($cs->class_id) }} · {{ $cs->stream_id ?: 'No stream' }}
                        </div>
                    @empty
                        <div class="text-muted">No Senior 1–4 class streams are set up for this school.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rc-panel">
            <div class="ph">
                <span><i class="fas fa-layer-group me-1 text-primary"></i> What goes on the card</span>
                <div class="rc-actions">
                    <button type="button" class="rc-action-btn rc-action-assess" id="addAssess">
                        <span class="rc-action-icon"><i class="fas fa-tasks"></i></span>
                        <span class="rc-action-text">Add assessments</span>
                    </button>
                    <button type="button" class="rc-action-btn rc-action-exam" id="addExam">
                        <span class="rc-action-icon"><i class="fas fa-file-signature"></i></span>
                        <span class="rc-action-text">Add examination</span>
                    </button>
                </div>
            </div>
            <div class="pb">
                <div id="components"></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="hint">
                        Each assessment can appear on any number of report cards. Subjects missing one part
                        (for example no exam mark) are scored out of the parts that exist.
                    </span>
                    <span class="total-pill" id="totalPill">Total: 0</span>
                </div>
            </div>
        </div>

        <div class="rc-footer-actions mb-5">
            <button type="button" class="rc-btn rc-btn-primary" id="saveBtn">
                <span class="rc-btn-icon"><i class="fas fa-save"></i></span>
                <span>{{ $isEdit ? 'Save changes' : 'Create report card' }}</span>
            </button>
            <a href="{{ route('olevel-report-cards.index') }}" class="rc-btn rc-btn-cancel">
                <span class="rc-btn-icon"><i class="fas fa-times"></i></span>
                <span>Cancel</span>
            </a>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            const IS_EDIT = @json($isEdit);
            const SAVE_URL = @json($isEdit ? route('olevel-report-cards.update', $card->id) : route('olevel-report-cards.store'));
            const ASSESS_URL = @json(route('olevel-report-cards.assessment-options'));
            const EXAM_URL = @json(route('olevel-report-cards.exam-options'));
            const CSRF = @json(csrf_token());

            let components = @json($components);   // [{type,label,weight,source_examination_id,assessment_ids}]
            let assessData = [];                    // [{exam_id, exam_name, term, year, subjects:[{subject, assessments:[...]}]}]
            let examData = [];                      // [{id, name, total_marks}]

            // open/closed state per component's exam groups: { "compIndex:examId": true }
            const openGroups = {};

            const $ = (id) => document.getElementById(id);
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const selectedClasses = () => [...document.querySelectorAll('.cs-chip.on')].map(e => e.dataset.value);

            components.forEach(c => { c.assessment_ids = (c.assessment_ids || []).map(Number); });

            function alertBox(msg, type = 'danger') {
                $('alertBox').innerHTML = msg ? `<div class="alert alert-${type}">${esc(msg)}</div>` : '';
                if (msg) window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            // ── load what can be picked for the ticked classes ──────────────
            let loadTimer = null;
            function loadOptions() {
                clearTimeout(loadTimer);
                loadTimer = setTimeout(async () => {
                    const classes = selectedClasses();
                    if (!classes.length) { assessData = []; examData = []; render(); return; }
                    const qs = classes.map(c => 'class_streams[]=' + encodeURIComponent(c)).join('&');
                    try {
                        const [a, e] = await Promise.all([
                            fetch(ASSESS_URL + '?' + qs, { headers: { 'Accept': 'application/json' } }).then(r => r.json()),
                            fetch(EXAM_URL + '?' + qs, { headers: { 'Accept': 'application/json' } }).then(r => r.json()),
                        ]);
                        assessData = a.exams || [];
                        examData = e.exams || [];
                        // drop ticks that no longer apply to the chosen classes
                        const valid = new Set(assessData.flatMap(x => x.subjects.flatMap(s => s.assessments.map(z => z.id))));
                        components.forEach(c => { if (c.type === 'assessments') c.assessment_ids = c.assessment_ids.filter(id => valid.has(id)); });
                        render();
                    } catch (err) { alertBox('Could not load assessments / examinations.'); }
                }, 250);
            }

            // ── render the component cards ──────────────────────────────────
            function render() {
                const host = $('components');
                if (!components.length) {
                    host.innerHTML = `<div class="text-center text-muted py-4">Nothing added yet — use <b>Add assessments</b> and/or <b>Add examination</b>.</div>`;
                    updateTotal(); return;
                }
                host.innerHTML = components.map((c, i) => c.type === 'exam' ? examCard(c, i) : assessCard(c, i)).join('');
                updateTotal();
            }

            function head(c, i, kind) {
                const isExam = kind === 'exam';
                const accent = isExam ? '#f59e0b' : '#2C29CA';
                const icon = isExam ? 'fa-file-signature' : 'fa-tasks';
                const title = isExam ? 'Examination part' : 'Assessments part';

                return `
                        <div class="comp-head">
                            <div class="comp-head-top">
                                <div class="comp-head-title">
                                    <span class="comp-head-icon" style="--accent:${accent};">
                                        <i class="fas ${icon}"></i>
                                    </span>
                                    <div>
                                        <div class="comp-head-label" style="color:${accent};">${title}</div>
                                        <div class="comp-head-sub">${isExam
                        ? 'A single exam, converted to the “Out of” value below.'
                        : 'Competency scores averaged and shown out of the “Out of” value below.'}
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="comp-remove" data-act="remove" data-i="${i}" title="Remove this part">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Remove</span>
                                </button>
                            </div>

                            <div class="comp-head-fields">
                                <div class="comp-field comp-field-label">
                                    <label>Column title on the report card</label>
                                    <div class="comp-input-wrap">
                                        <i class="fas fa-heading comp-input-icon"></i>
                                        <input type="text" class="form-control form-control-sm"
                                            data-field="label" data-i="${i}" maxlength="80"
                                            placeholder="e.g. ${isExam ? 'End of Term Exam' : 'Continuous Assessment'}"
                                            value="${esc(c.label)}">
                                    </div>
                                </div>
                                <div class="comp-field comp-field-weight">
                                    <label>Out of (marks)</label>
                                    <div class="comp-input-wrap">
                                        <i class="fas fa-hashtag comp-input-icon"></i>
                                        <input type="number" class="form-control form-control-sm"
                                            data-field="weight" data-i="${i}" min="1" max="1000" step="0.5"
                                            value="${esc(c.weight)}">
                                        <span class="comp-input-suffix">pts</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            }

            function examCard(c, i) {
                const opts = examData.map(e => `<option value="${e.id}" ${Number(c.source_examination_id) === e.id ? 'selected' : ''}>${esc(e.name)} (out of ${e.total_marks})</option>`).join('');
                const empty = !examData.length ? `<div class="hint text-danger mt-1">No standard examination sits the ticked classes. Create one (Create Exam → “Standard examination”) first.</div>` : '';
                return `<div class="comp is-exam">${head(c, i, 'exam')}
                                        <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Examination</label>
                                        <select class="form-select form-control" data-field="source_examination_id" data-i="${i}">
                                            <option value="">-- Choose examination --</option>${opts}
                                        </select>${empty}
                                        <div class="hint mt-1">Each subject's mark is converted to the “Out of” value above.</div>
                                    </div>`;
            }

            function assessCard(c, i) {
                const set = new Set(c.assessment_ids);
                let body = '';

                if (!selectedClasses().length) {
                    body = `<div class="hint text-danger">No class has been selected. Tick a class above to see available assessments.</div>`;
                } else if (!assessData.length) {
                    body = `<div class="hint text-danger">No assessments exist for the ticked classes yet.</div>`;
                } else {
                    assessData.forEach(ex => {
                        const ids = ex.subjects.flatMap(s => s.assessments.map(a => a.id));
                        const all = ids.length && ids.every(id => set.has(id));
                        const picked = ids.filter(id => set.has(id)).length;
                        const key = i + ':' + ex.exam_id;
                        const isOpen = !!openGroups[key];

                        body += `<div class="exam-group ${isOpen ? 'open' : ''} ${picked ? 'active' : ''}" data-group="${key}">
                                                <div class="exam-head" data-act="toggle-group" data-key="${key}">
                                                    <span class="caret"><i class="fas fa-chevron-right"></i></span>
                                                    <label class="toggle-switch sm" data-switch="1">
                                                        <input type="checkbox" data-act="pick-exam" data-i="${i}" data-exam="${ex.exam_id}" ${all ? 'checked' : ''}>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <div>
                                                        <div class="exam-name">${esc(ex.exam_name)}</div>
                                                        <div class="exam-meta">${esc(ex.term)} ${esc(ex.year)} · all ${ids.length} assessments</div>
                                                    </div>
                                                    <span class="exam-count">${picked}/${ids.length}</span>
                                                </div>
                                                <div class="exam-body">`;

                        ex.subjects.forEach(s => {
                            body += `<div class="pick-subject-head">${esc(s.subject)}</div>`;
                            s.assessments.forEach(a => {
                                body += `<label class="toggle-row">
                                                        <div class="toggle-info">
                                                            <div class="toggle-title">${esc(a.type)} — ${esc(a.title)}</div>
                                                            <div class="toggle-meta">${esc(a.class)}${a.max_marks ? ' · out of ' + esc(a.max_marks) : ''}</div>
                                                        </div>
                                                        <span class="toggle-switch">
                                                            <input type="checkbox" data-act="pick" data-i="${i}" data-id="${a.id}" ${set.has(a.id) ? 'checked' : ''}>
                                                            <span class="slider"></span>
                                                        </span>
                                                    </label>`;
                            });
                        });

                        body += `</div></div>`;
                    });
                }

                return `<div class="comp is-assess">${head(c, i, 'assess')}
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold mb-0" style="font-size:.78rem;">Choose assessments (${c.assessment_ids.length} selected)</label>
                                            <button type="button" class="btn btn-sm btn-link p-0" data-act="clear" data-i="${i}">Clear all</button>
                                        </div>
                                        <div class="pick-box">${body}</div>
                                        <div class="hint mt-1">Scores (0–3) are averaged per subject and shown out of the “Out of” value above.</div>
                                    </div>`;
            }

            function updateTotal() {
                const t = components.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0);
                const pill = $('totalPill');
                pill.textContent = 'Total: ' + (Math.round(t * 100) / 100);
                pill.classList.toggle('off', Math.abs(t - 100) > 0.001);
                pill.title = Math.abs(t - 100) > 0.001 ? 'Not 100 — fine if you want the card out of a different total.' : '';
            }

            // ── events ──────────────────────────────────────────────────────
            $('csChips').addEventListener('click', e => {
                const chip = e.target.closest('.cs-chip'); if (!chip) return;
                chip.classList.toggle('on'); loadOptions();
            });
            $('addAssess').onclick = () => { components.push({ type: 'assessments', label: 'Assessment', weight: 20, source_examination_id: null, assessment_ids: [] }); render(); };
            $('addExam').onclick = () => { components.push({ type: 'exam', label: 'Examination', weight: 80, source_examination_id: null, assessment_ids: [] }); render(); };

            $('components').addEventListener('input', e => {
                const f = e.target.dataset.field; if (!f) return;
                const c = components[+e.target.dataset.i];
                c[f] = f === 'source_examination_id' ? (e.target.value || null) : e.target.value;
                if (f === 'weight') updateTotal();
            });
            $('components').addEventListener('change', e => {
                const act = e.target.dataset.act; if (!act) return;
                const c = components[+e.target.dataset.i];
                const set = new Set(c.assessment_ids);
                if (act === 'pick') { e.target.checked ? set.add(+e.target.dataset.id) : set.delete(+e.target.dataset.id); }
                if (act === 'pick-exam') {
                    const ex = assessData.find(x => x.exam_id === +e.target.dataset.exam);
                    if (!ex) return;
                    ex.subjects.forEach(s => s.assessments.forEach(a => e.target.checked ? set.add(a.id) : set.delete(a.id)));
                }
                c.assessment_ids = [...set]; render();
            });
            $('components').addEventListener('click', e => {
                // Click on the switch inside an exam-head: let `change` handle it, don't toggle group.
                const sw = e.target.closest('.exam-head .toggle-switch');
                if (sw) return;

                // Click anywhere else on the exam-head: open/close the group.
                const headEl = e.target.closest('.exam-head');
                if (headEl) {
                    const key = headEl.dataset.key;
                    openGroups[key] = !openGroups[key];
                    const groupEl = headEl.closest('.exam-group');
                    if (groupEl) groupEl.classList.toggle('open', openGroups[key]);
                    return;
                }

                const b = e.target.closest('[data-act]'); if (!b || b.tagName === 'INPUT') return;
                const i = +b.dataset.i;
                if (b.dataset.act === 'remove') { components.splice(i, 1); render(); }
                if (b.dataset.act === 'clear') { components[i].assessment_ids = []; render(); }
            });

            // ── save ────────────────────────────────────────────────────────
            $('saveBtn').onclick = async () => {
                alertBox('');
                const body = {
                    exam_name: $('examName').value.trim(),
                    term: $('term').value,
                    academic_year: $('academicYear').value,
                    grading_scheme_id: $('gradingScheme').value,
                    description: $('description').value.trim() || null,
                    class_streams: selectedClasses(),
                    components: components.map(c => ({
                        type: c.type, label: c.label, weight: c.weight,
                        source_examination_id: c.type === 'exam' ? c.source_examination_id : null,
                        assessment_ids: c.type === 'assessments' ? c.assessment_ids : [],
                    })),
                };
                if (!body.exam_name) return alertBox('Give the report card a name.');
                if (!body.class_streams.length) return alertBox('Tick at least one class.');
                if (!body.components.length) return alertBox('Add at least one part (assessments or an examination).');

                const btn = $('saveBtn'); btn.disabled = true;
                try {
                    const res = await fetch(SAVE_URL, {
                        method: IS_EDIT ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success) { window.location.href = data.redirect; return; }
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    alertBox(first || data.message || 'Could not save the report card.');
                } catch (err) { alertBox('Could not save the report card.'); }
                btn.disabled = false;
            };

            render();
            loadOptions();
        })();
    </script>
@endsection