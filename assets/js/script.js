// assets/js/script.js

// Add confirmation popup before deleting items
document.addEventListener("DOMContentLoaded", function() {
    const deleteLinks = document.querySelectorAll(".del-link");
    
    deleteLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            const isConfirmed = confirm("Are you sure you want to delete this record? This action cannot be undone.");
            if (!isConfirmed) {
                e.preventDefault(); // Stop navigation if not confirmed
            }
        });
    });
});

// Function to handle switching tabs in dashboards
function showTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all menu buttons
    document.querySelectorAll('.menu-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show the selected tab
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Highlight the clicked button
    const activeBtn = document.querySelector(`.menu-btn[onclick="showTab('${tabId}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Update URL without reloading (to keep track of active tab on refresh if needed)
    window.history.replaceState(null, null, "?tab=" + tabId);
}
