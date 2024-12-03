<!-- page/game_settings.php -->
<div class="container my-4">
    <h2>Game Settings</h2>
    <form id="gameSettingsForm">
        <!-- Example game setting: Toggle Sound -->
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="toggleSound" name="toggle_sound">
            <label class="form-check-label" for="toggleSound">Enable Sound</label>
        </div>
        <!-- Example game setting: Difficulty Level -->
        <div class="mb-3">
            <label for="difficultyLevel" class="form-label">Difficulty Level</label>
            <select class="form-select" id="difficultyLevel" name="difficulty_level">
                <option value="easy">Easy</option>
                <option value="normal" selected>Normal</option>
                <option value="hard">Hard</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Game Settings</button>
    </form>
    <div id="gameSettingsMessage" class="mt-3"></div>
</div>