<div class="container mt-5">
        <h3 class="text-center mb-4" style="color: #1f4e79;">Ticket Management</h3>
        
        <!-- Tickets List -->
        <div class="table-responsive">
            <ul class="list-group ticket-list">
                <?php if ($_SESSION['authority'] == 0): ?>
                    <li class="list-group-item">My Tickets</li>
                <?php else: ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Tickets</span>
                        <button id="newTicketButton" class="btn btn-primary" data-bs-toggle="tooltip" title="Send a New Ticket">
                            <i class="fas fa-plus"></i>
                        </button>
                    </li>
                <?php endif; ?>
                <div id="ticketContent">
                    <!-- Ticket items will be dynamically loaded here by PHP -->
                </div>
            </ul>
        </div>

        <!-- New Ticket Form (only for admins) -->
        <?php if ($_SESSION['authority'] >= 5): ?>
            <div class="mt-4">
                <h5>Send a New Ticket</h5>
                <form id="newTicketForm" method="post" action="../inc/ticket.php">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="reported_player" class="form-label">Reported Player Name</label>
                        <input type="text" class="form-control" id="reported_player" name="reported_player" required>
                    </div>
                    <?php if ($_SESSION['authority'] >= 5): ?>
                        <div class="mb-3">
                            <label for="reported_account" class="form-label">Reported Account Name</label>
                            <input type="text" class="form-control" id="reported_account" name="reported_account">
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_evidence" class="form-label">Image Evidence (URL)</label>
                        <input type="url" class="form-control" id="image_evidence" name="image_evidence">
                    </div>
                    <div class="mb-3">
                        <label for="video_evidence" class="form-label">Video Evidence (URL)</label>
                        <input type="url" class="form-control" id="video_evidence" name="video_evidence">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </form>
            </div>
        <?php endif; ?>
    </div>