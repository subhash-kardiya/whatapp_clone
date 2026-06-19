<div class="wallpaper-picker-block">
    <div class="wallpaper-upload-row">
        <button type="button"
            class="wallpaper-upload-card"
            :class="{ active: isWallpaperImageActive('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }}) }"
            :style="getWallpaperThumbStyle('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }})"
            @click="$refs.{{ $refName }}.click()">
            <template x-if="!getWallpaperImage('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }})">
                <span class="wallpaper-upload-inner">
                    <span class="wallpaper-upload-icon">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
                        </svg>
                    </span>
                    <span class="wallpaper-upload-label">Upload photo</span>
                    <span class="wallpaper-upload-hint">JPG, PNG, WebP</span>
                </span>
            </template>
            <template x-if="getWallpaperImage('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }})">
                <span class="wallpaper-upload-inner wallpaper-upload-has-image">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="white">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                    </svg>
                    Change photo
                </span>
            </template>
        </button>
    </div>
    <input type="file" x-ref="{{ $refName }}" class="sr-only-input" accept="image/jpeg,image/png,image/webp,image/gif"
        @change="handleWallpaperUpload($event, '{{ $scope }}', '{{ $entityType }}', {{ $idExpr }})">

    <div class="wallpaper-themes-label">
        <span>Color themes</span>
        <button type="button" class="wallpaper-reset-link"
            x-show="getWallpaperImage('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }}) || isCustomWallpaperSet('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }})"
            @click="resetWallpaper('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }}, '{{ $defaultPreset }}')">
            Reset default
        </button>
    </div>

    <div class="wallpaper-picker-scroll">
        <div class="wallpaper-picker-grid">
            <template x-for="preset in wallpaperPresets" :key="'{{ $refName }}-'+preset.id">
                <button type="button" class="wallpaper-swatch"
                    :class="['wallpaper-' + preset.id, { active: isWallpaperPresetActive(preset.id, '{{ $scope }}', '{{ $entityType }}', {{ $idExpr }}, '{{ $defaultPreset }}') }]"
                    @click="setWallpaperPreset('{{ $scope }}', '{{ $entityType }}', {{ $idExpr }}, preset.id)"
                    :title="preset.label">
                    <span class="wallpaper-swatch-name" x-text="preset.label"></span>
                </button>
            </template>
        </div>
    </div>
</div>