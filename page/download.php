<?php require_once '../inc/configuration.php'; ?>
<div class="download-section">

    <!-- Download Buttons with Services -->
    <div class="row justify-content-center text-center mb-5">
        <?php if (!empty(GOOGLE_DRIVE_LINK)): ?>
            <div class="col-md-3 mb-3">
                <a href="<?php echo GOOGLE_DRIVE_LINK; ?>" target="_blank" class="custom-download-card">
                    <div class="download-icon">
                        <img src="../img/gdrive.png" alt="Google Drive" class="download-icon-img">
                    </div>
                    <div class="download-info">
                        <h4>Google Drive</h4>
                        <div class="info-row">
                            <span class="info-label">Ver.</span>
                            <span class="info-value"><?php echo VERSION; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Size</span>
                            <span class="info-value"><?php echo SIZE; ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty(MEDIAFIRE_LINK)): ?>
            <div class="col-md-3 mb-3">
                <a href="<?php echo MEDIAFIRE_LINK; ?>" target="_blank" class="custom-download-card">
                    <div class="download-icon">
                        <img src="../img/mediafire.png" alt="Mediafire" class="download-icon-img">
                    </div>
                    <div class="download-info">
                        <h4>Mediafire</h4>
                        <div class="info-row">
                            <span class="info-label">Ver.</span>
                            <span class="info-value"><?php echo VERSION; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Size</span>
                            <span class="info-value"><?php echo SIZE; ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty(MEGA_LINK)): ?>
            <div class="col-md-3 mb-3">
                <a href="<?php echo MEGA_LINK; ?>" target="_blank" class="custom-download-card">
                    <div class="download-icon">
                        <img src="../img/mega.png" alt="MEGA" class="download-icon-img">
                    </div>
                    <div class="download-info">
                        <h4>MEGA</h4>
                        <div class="info-row">
                            <span class="info-label">Ver.</span>
                            <span class="info-value"><?php echo VERSION; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Size</span>
                            <span class="info-value"><?php echo SIZE; ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty(GOFILE_LINK)): ?>
            <div class="col-md-3 mb-3">
                <a href="<?php echo GOFILE_LINK; ?>" target="_blank" class="custom-download-card">
                    <div class="download-icon">
                        <img src="../img/gofile.png" alt="GoFile" class="download-icon-img">
                    </div>
                    <div class="download-info">
                        <h4>GoFile</h4>
                        <div class="info-row">
                            <span class="info-label">Ver.</span>
                            <span class="info-value"><?php echo VERSION; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Size</span>
                            <span class="info-value"><?php echo SIZE; ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- System Requirements Table -->
<div class="row">
    <div class="col-12">
        <div class="page-title">
            <span>System Requirements</span>
        </div>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Component</th>
                    <th>Minimum</th>
                    <th>Recommended</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>OS</strong></td>
                    <td>Windows XP, Windows Vista, Windows 7 (32bit/64bit), Windows 8</td>
                    <td>Windows XP, Windows Vista, Windows 7 (32bit/64bit), Windows 8</td>
                </tr>
                <tr>
                    <td><strong>Processor</strong></td>
                    <td>Intel Pentium4 2.8 GHz or better; AMD K8 2600 or better</td>
                    <td>Intel Core2 Duo 2.66 GHz or equivalent; AMD Athlon 64 X2 6000 or equivalent</td>
                </tr>
                <tr>
                    <td><strong>Memory</strong></td>
                    <td>4 GB RAM</td>
                    <td>4 GB RAM</td>
                </tr>
                <tr>
                    <td><strong>Graphics</strong></td>
                    <td>nVidia GeForce 6600 or better; ATI Redeon X1600 or better</td>
                    <td>nVidia GeForce 9500 or equivalent; ATI Radeon HD2600 or equivalent</td>
                </tr>
                <tr>
                    <td><strong>DirectX</strong></td>
                    <td>Version 9.0c</td>
                    <td>Version 9.0c</td>
                </tr>
                <tr>
                    <td><strong>Storage</strong></td>
                    <td>6 GB available space</td>
                    <td>6 GB available space</td>
                </tr>
            </tbody>
        </table>
        <p class="text-muted text-center mt-2">* Game compatibility may vary based on system updates and configurations.</p>
    </div>
</div>
</div>