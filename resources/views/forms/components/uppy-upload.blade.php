@php
    $config = $field->getUppyConfig();
    $statePath = $field->getStatePath();
    $isDisabled = $field->isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="uppyUpload({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            config: @js($config),
        })"
        class="uppy-upload-wrapper"
        @if($isDisabled) data-disabled="true" @endif
    >
        <div x-show="isLoading" class="flex items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
            <div class="flex flex-col items-center gap-2">
                <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('uppy-upload::uppy.loading') }}</span>
            </div>
        </div>

        <div x-ref="uppyDashboard" x-show="!isLoading" x-transition class="uppy-dashboard-container"></div>
    </div>

    <style>
        .uppy-upload-wrapper { width: 100%; }
        .uppy-upload-wrapper[data-disabled="true"] { opacity: 0.6; pointer-events: none; }
        .uppy-dashboard-container .uppy-Dashboard-inner { border-radius: 0.5rem; }

        /*
         * CRITICAL: Force color-scheme on the Uppy container.
         * Uppy's CSS uses @media (prefers-color-scheme: dark) which follows the OS,
         * not Filament's theme. This forces Uppy to respect Filament's theme instead.
         *
         * When Filament is in LIGHT mode (no .dark on <html>):
         *   → force color-scheme: light so OS dark mode doesn't leak in
         *
         * When Filament is in DARK mode (.dark on <html>):
         *   → force color-scheme: dark so Uppy's dark media queries activate
         */
        html:not(.dark) .uppy-upload-wrapper,
        html:not(.dark) .uppy-upload-wrapper * {
            color-scheme: light only !important;
        }
        html.dark .uppy-upload-wrapper,
        html.dark .uppy-upload-wrapper * {
            color-scheme: dark only !important;
        }

        /* ── Light mode: undo any OS-dark-mode overrides that sneak through ── */
        html:not(.dark) .uppy-Dashboard-inner {
            background-color: #fff !important;
            border-color: #e5e7eb !important;
            color: #333 !important;
        }
        html:not(.dark) .uppy-Dashboard-AddFiles {
            border-color: #e5e7eb !important;
            background-color: transparent !important;
        }
        html:not(.dark) .uppy-Dashboard-AddFiles-title {
            color: #333 !important;
        }
        html:not(.dark) .uppy-DashboardTab-name {
            color: #555 !important;
        }
        html:not(.dark) .uppy-Dashboard-note {
            color: #757575 !important;
        }
        html:not(.dark) .uppy-StatusBar {
            background-color: #fff !important;
            border-color: #e5e7eb !important;
        }
        html:not(.dark) .uppy-StatusBar-statusPrimary {
            color: #333 !important;
        }
        html:not(.dark) .uppy-Dashboard-Item-name {
            color: #333 !important;
        }
        html:not(.dark) .uppy-Dashboard-Item-statusSize {
            color: #757575 !important;
        }
        html:not(.dark) .uppy-Dashboard-Item {
            border-color: #e5e7eb !important;
        }

        /* ── Dark mode overrides (only when Filament has .dark class) ── */
        html.dark .uppy-Dashboard-inner {
            background-color: #1f2937 !important;
            border-color: #4b5563 !important;
        }
        html.dark .uppy-Dashboard-AddFiles {
            border-color: #6b7280 !important;
            background-color: transparent !important;
        }
        html.dark .uppy-Dashboard-AddFiles-title {
            color: #e5e7eb !important;
        }
        html.dark .uppy-Dashboard-AddFiles-title button,
        html.dark .uppy-Dashboard-AddFiles-title a {
            color: #60a5fa !important;
        }
        html.dark .uppy-DashboardTab-name {
            color: #d1d5db !important;
        }
        html.dark .uppy-Dashboard-note {
            color: #9ca3af !important;
        }
        html.dark .uppy-StatusBar {
            background-color: #1f2937 !important;
            border-color: #4b5563 !important;
        }
        html.dark .uppy-StatusBar-statusPrimary {
            color: #d1d5db !important;
        }
        html.dark .uppy-StatusBar-statusSecondary {
            color: #9ca3af !important;
        }
        html.dark .uppy-Dashboard-Item {
            border-color: #4b5563 !important;
        }
        html.dark .uppy-Dashboard-Item-name {
            color: #e5e7eb !important;
        }
        html.dark .uppy-Dashboard-Item-statusSize,
        html.dark .uppy-Dashboard-Item-status {
            color: #9ca3af !important;
        }
        html.dark .uppy-Dashboard-FileCard,
        html.dark .uppy-Dashboard-FileCard-inner {
            background-color: #1f2937 !important;
        }
        html.dark .uppy-Dashboard-FileCard-info {
            color: #e5e7eb !important;
        }
        html.dark .uppy-StatusBar-actionBtn--upload {
            background-color: #2563eb !important;
            color: #fff !important;
        }
        html.dark .uppy-Informer {
            background-color: #374151 !important;
        }
        html.dark .uppy-Informer-animated {
            color: #e5e7eb !important;
        }
        html.dark .uppy-DashboardContent-addMore,
        html.dark .uppy-DashboardContent-addMoreCaption {
            color: #9ca3af !important;
        }
        html.dark .uppy-DashboardContent-addMore svg {
            fill: #9ca3af !important;
        }
        html.dark .uppy-ProviderBrowser,
        html.dark .uppy-ProviderBrowserItem {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
        html.dark .uppy-ProviderBrowser-header {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        /* Webcam source dropdown - fix readability in both modes */
        html:not(.dark) .uppy-Webcam-videoSource-select,
        html:not(.dark) .uppy-Webcam-audioSource-select {
            background-color: #fff !important;
            color: #333 !important;
            border-color: #d1d5db !important;
        }
        html.dark .uppy-Webcam-videoSource-select,
        html.dark .uppy-Webcam-audioSource-select {
            background-color: #374151 !important;
            color: #e5e7eb !important;
            border-color: #4b5563 !important;
        }

        /* Webcam container backgrounds */
        html:not(.dark) .uppy-Webcam-container {
            background-color: #f9fafb !important;
        }
        html:not(.dark) .uppy-Webcam-title {
            color: #333 !important;
        }
        html:not(.dark) .uppy-Webcam-permissons p {
            color: #555 !important;
        }

        /* Provider/panel header text */
        html:not(.dark) .uppy-DashboardContent-title {
            color: #333 !important;
        }
        html:not(.dark) .uppy-DashboardContent-back {
            color: #2563eb !important;
        }

        /* Z-index for modals within Filament modals */
        .fi-modal .uppy-Dashboard--modal .uppy-Dashboard-overlay { z-index: 9998 !important; }
        .fi-modal .uppy-Dashboard--modal .uppy-Dashboard-inner { z-index: 9999 !important; }
        .uppy-ImageCropper-container { z-index: 10000 !important; }
    </style>
</x-dynamic-component>
