/**
 * PIETECH Events Platform JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap Tooltips Initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Event Category Filter
    const filterButtons = document.querySelectorAll('.category-btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active state
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Get category value
                const category = this.dataset.category;
                const eventCards = document.querySelectorAll('.event-item');
                
                // Filter events
                eventCards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Update counter
                const visibleCount = document.querySelectorAll('.event-item[style="display: block;"]').length;
                const counterElement = document.getElementById('event-counter');
                if (counterElement) {
                    counterElement.textContent = `Showing ${visibleCount} event${visibleCount !== 1 ? 's' : ''}`;
                }
            });
        });
    }
    
    // Team Registration Form Toggle
    const teamBasedCheckbox = document.getElementById('team_based');
    const teamMembersDiv = document.getElementById('team_members_div');
    
    if (teamBasedCheckbox && teamMembersDiv) {
        // Initial state
        teamMembersDiv.style.display = teamBasedCheckbox.checked ? 'block' : 'none';
        
        // Toggle on change
        teamBasedCheckbox.addEventListener('change', function() {
            teamMembersDiv.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Attendance Toggle
    const attendanceToggles = document.querySelectorAll('.attendance-toggle');
    if (attendanceToggles.length > 0) {
        attendanceToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const registrationId = this.dataset.registrationId;
                const checkIn = this.checked ? 1 : 0;
                
                // Send AJAX request to update attendance
                fetch('update_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `registration_id=${registrationId}&check_in=${checkIn}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the badge
                        const badge = document.querySelector(`.attendance-badge-${registrationId}`);
                        if (badge) {
                            badge.className = checkIn ? 
                                'badge bg-success attendance-badge-' + registrationId : 
                                'badge bg-danger attendance-badge-' + registrationId;
                            badge.textContent = checkIn ? 'Present' : 'Absent';
                        }
                        
                        // Update the summary
                        updateAttendanceSummary();
                    } else {
                        alert('Failed to update attendance: ' + data.message);
                        // Revert the toggle
                        this.checked = !this.checked;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating attendance');
                    // Revert the toggle
                    this.checked = !this.checked;
                });
            });
        });
    }
    
    // Function to update attendance summary
    function updateAttendanceSummary() {
        const totalAttended = document.querySelectorAll('.attendance-toggle:checked').length;
        const totalRegistered = document.querySelectorAll('.attendance-toggle').length;
        
        const summaryElement = document.getElementById('attendance-summary');
        if (summaryElement) {
            summaryElement.textContent = `${totalAttended} attended / ${totalRegistered} registered`;
        }
    }
    
    // Date input validation
    const dateInputs = document.querySelectorAll('input[type="date"]');
    if (dateInputs.length > 0) {
        const today = new Date().toISOString().split('T')[0];
        
        dateInputs.forEach(input => {
            // Set min date to today for future events
            if (input.classList.contains('future-date')) {
                input.setAttribute('min', today);
            }
        });
    }
}); 