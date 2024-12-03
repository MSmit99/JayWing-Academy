// availability.js
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentAvailability();
    initializeAvailabilityHandlers();
});

function loadCurrentAvailability() {
    fetch('../data_src/api/availability/get_availability.php')
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Error loading availability:', result.message);
                return;
            }

            const availability = result.data;
            const tbody = document.getElementById('availabilityTableBody');
            tbody.innerHTML = '';
            
            if (Array.isArray(availability)) {
                availability.forEach(slot => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${slot.weekday}</td>
                        <td>${formatTime(slot.start)}</td>
                        <td>${formatTime(slot.end)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-availability" 
                                    data-availability-id="${slot.availability_id}">
                                <i class="fas fa-times"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Add delete handlers
                document.querySelectorAll('.delete-availability').forEach(button => {
                    button.addEventListener('click', function() {
                        const availabilityId = this.getAttribute('data-availability-id');
                        deleteAvailability(availabilityId);
                    });
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteAvailability(availabilityId) {
    if (confirm('Are you sure you want to delete this availability slot?')) {
        fetch('../data_src/api/availability/delete_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ availability_id: availabilityId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCurrentAvailability();
                alert('Availability slot deleted successfully');
            } else {
                alert('Failed to delete availability slot');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the availability slot');
        });
    }
}

function initializeAvailabilityHandlers() {
    const form = document.getElementById('availabilityForm');
    const addButton = document.getElementById('addTimeSlot');
    const toggleButton = document.getElementById('toggleAvailability');

    // Add new time slot
    addButton.addEventListener('click', function() {
        const template = document.querySelector('.availability-entry').cloneNode(true);
        template.querySelector('select').value = '';
        template.querySelectorAll('input').forEach(input => input.value = '');
        document.getElementById('availabilityInputs').appendChild(template);
        
        // Add remove handler to new row
        template.querySelector('.remove-time').addEventListener('click', function() {
            if (document.querySelectorAll('.availability-entry').length > 1) {
                this.closest('.availability-entry').remove();
            }
        });
    });

    // Remove time slot
    document.querySelectorAll('.remove-time').forEach(button => {
        button.addEventListener('click', function() {
            if (document.querySelectorAll('.availability-entry').length > 1) {
                this.closest('.availability-entry').remove();
            }
        });
    });

    // Toggle availability status
    toggleButton.addEventListener('click', function() {
        // Get current status from button text
        const isCurrentlyAvailable = this.textContent.includes('Unavailable');
        
        // Confirmation message
        const message = isCurrentlyAvailable ? 
            'Are you sure you want to set yourself as unavailable? You will not be visible for tutoring until you set yourself as available again.' :
            'Are you sure you want to set yourself as available? You will be visible for tutoring sessions.';
        
        if (confirm(message)) {
            fetch('../data_src/api/availability/toggle_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isAvailable = data.isAvailable;
                    
                    // Update button text and class
                    this.textContent = isAvailable ? 'Set Unavailable' : 'Set Available';
                    
                    // Toggle button classes
                    if (isAvailable) {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-danger');
                    } else {
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-success');
                    }
                    
                    // Show status message
                    const statusMessage = isAvailable ? 
                        'You are now set as available for tutoring.' :
                        'You are now set as unavailable for tutoring.';
                    alert(statusMessage);
                } else {
                    alert('Failed to update availability status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating availability status.');
            });
        }
    });

    // Submit availability form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const availability = {
            slots: []
        };
    
        const weekdays = formData.getAll('weekday[]');
        const starts = formData.getAll('start[]');
        const ends = formData.getAll('end[]');
    
        for (let i = 0; i < weekdays.length; i++) {
            if (weekdays[i] && starts[i] && ends[i]) {  // Only add if all fields are filled
                availability.slots.push({
                    weekday: weekdays[i],
                    start: starts[i],
                    end: ends[i]
                });
            }
        }
    
        fetch('../data_src/api/availability/update_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(availability)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCurrentAvailability();
                if (data.warnings && data.warnings.length > 0) {
                    // Show warnings if any
                    alert('Availability updated with some warnings:\n' + data.warnings.join('\n'));
                } else {
                    alert(data.message);
                }
            } else {
                alert('Failed to update availability: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating availability');
        });
    });
}

function formatTime(timeString) {
    return new Date('2000-01-01T' + timeString)
        .toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}