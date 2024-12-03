<div class="modal fade" id="addNewsModal" tabindex="-1" aria-labelledby="addNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addNewsForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewsModalLabel">Add News</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="addNewsMessage"></div>
                    <div class="mb-3">
                        <label for="newsTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="newsTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="newsCategory" class="form-label">Category</label>
                        <select class="form-control" id="newsCategory" name="category" required>
                            <option value="update">Update</option>
                            <option value="important">Important</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newsContent" class="form-label">Content</label>
                        <textarea class="form-control" id="newsContent" name="content" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn ak-button">Submit</button>
                    <button type="button" class="btn ak-button" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>