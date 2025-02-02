
// Function to show alert and redirect
function showSuccessMessageAndRedirect(message, redirectUrl) {
    setTimeout(() => {window.location.href = redirectUrl;}, 2000); }// redirect after 2 seconds

    function confirmDelete(vendor_id) {
        if (!confirm("Are you sure you want to delete this vendor?")) {
            alert("Deletion cancelled. The vendor was not deleted.");
            return false; // Stop the form submission
        }
    
        // Submit the form only if the user confirms
        document.getElementById("delete-form-" + vendor_id).submit();
    }
    
