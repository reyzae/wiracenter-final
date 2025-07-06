<?php
$page_title = 'Help & Documentation';
include 'includes/header.php';

requireLogin();
// This page is accessible to editors and admins
if (!hasPermission('editor') && !hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}
?>

<h1 class="h2 mb-4">Help & Documentation</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Welcome to the Help Panel!</h5>
    </div>
    <div class="card-body">
        <p>This section provides guidance on how to use the CMS effectively. Below are some common topics.</p>
    </div>
</div>

<div class="accordion" id="helpAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                Managing Articles
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p><strong>Creating a New Article:</strong></p>
                <ul>
                    <li>Navigate to 'Articles' from the sidebar.</li>
                    <li>Click on the 'New Article' button.</li>
                    <li>Fill in the 'Title' (required).</li>
                    <li>The 'Slug' will be auto-generated, but you can customize it.</li>
                    <li>Add a 'Short Description' (Excerpt) or leave it blank for auto-generation.</li>
                    <li>Write your main content in the 'Full Content' editor.</li>
                    <li>Select the 'Status' (Draft, Published, Scheduled, Archived).</li>
                    <li>Choose a 'Publish Date' if you want to schedule it.</li>
                    <li>Upload a 'Featured Image'.</li>
                    <li>Click 'Create Article' to save.</li>
                </ul>
                <p><strong>Editing an Existing Article:</strong></p>
                <ul>
                    <li>From the 'Articles' list, click 'Edit' next to the article you want to modify.</li>
                    <li>Make your changes and click 'Update Article'.</li>
                </ul>
                <p><strong>Bulk Actions:</strong></p>
                <ul>
                    <li>On the 'Articles' list, check the boxes next to the articles you want to modify.</li>
                    <li>Select an action from the 'Bulk Actions' dropdown (e.g., Delete, Change Status).</li>
                    <li>Click 'Apply'.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                Managing Projects
            </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p>Similar to articles, you can create, edit, and manage your projects. Remember to fill in project-specific details like Project URL, GitHub URL, and Technologies.</p>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Managing Tools
            </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p>Manage your tools and resources. Each tool can have a title, description, content, URL, and category.</p>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFour">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                Media Library
            </button>
        </h2>
        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p>Upload and manage your images and PDF files. You can search, filter, and copy URLs for easy embedding in your content.</p>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFive">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                Site Settings
            </button>
        </h2>
        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p>Configure general site information, homepage content, about page details, social media links, theme mode (light/dark), and debug settings.</p>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSix">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                Activity Logs
            </button>
        </h2>
        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#helpAccordion">
            <div class="accordion-body">
                <p>View a detailed record of all user activities within the CMS, including logins, content changes, and file uploads.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>