
// Function to show alert and redirect
function showSuccessMessageAndRedirect(message, redirectUrl) {
    setTimeout(() => {window.location.href = redirectUrl;}, 2000); }// redirect after 2 seconds

// Function to confirm deletion
function confirmDelete(vendor_id) {
    if (confirm("Are you sure you want to delete this vendor?")) {
        window.location.href = "?delete_id=" + vendor_id + ")";}} // Redirect with delete_id