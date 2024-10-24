
  // Make calendar variable global
let calendar;

// calendar.js
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  if (!calendarEl) {
      console.error('Calendar element not found');
      return;
  }

  calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
          left: 'prev,next today' + (isLoggedIn ? ' createEvent' : ''),
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: {
          url: '/jaywing-academy/src/data_src/api/events/get_events.php',
          method: 'GET',
          failure: function() {
              alert('There was an error while fetching events!');
          }
      },
      customButtons: {
          createEvent: {
              text: 'Create Event',
              click: function() {
                  if (typeof bootstrap !== 'undefined') {
                      var modal = new bootstrap.Modal(document.getElementById('createEventModal'));
                      modal.show();
                  }
              }
          }
      },
      eventClick: function(info) {
          showEventDetails(info.event);
      },
      eventDidMount: function(info) {
          // Add tooltip with full title
          new bootstrap.Tooltip(info.el, {
              title: info.event.title,
              placement: 'top',
              trigger: 'hover',
              container: 'body'
          });
      },
      editable: true,
      eventDrop: function(info) {
          updateEventDates(info.event);
      }
  });
  
  calendar.render();

    // Form validation and submission handling
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        // Add time validation
        createEventForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const startTime = new Date(document.getElementById('startDateTime').value);
            const endTime = new Date(document.getElementById('endDateTime').value);
            
            if (endTime <= startTime) {
                alert('End time must be after start time');
                return;
            }
            
            const formData = new FormData(this);
            const isEditing = formData.get('event_id') ? true : false;
            
            try {
                const response = await fetch(
                    isEditing ? 
                    '/jaywing-academy/src/data_src/api/events/update_event.php' :
                    '/jaywing-academy/src/data_src/api/events/create_event.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );
                
                const data = await response.json();
                
                if (data.success) {
                    calendar.refetchEvents();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
                    if (modal) {
                        modal.hide();
                    }
                    createEventForm.reset();
                    // Reset hidden event_id if it exists
                    const eventIdInput = document.getElementById('eventId');
                    if (eventIdInput) eventIdInput.value = '';
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error saving event:', error);
                alert('Failed to save event. Please try again.');
            }
        });
    }
});

// Function to edit event
async function editEvent(eventId) {
  try {
      const response = await fetch(`/jaywing-academy/src/data_src/api/events/get_event_details.php?event_id=${eventId}`);
      const data = await response.json();
      
      if (data.success) {
          // Hide details modal
          const detailsModal = bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal'));
          detailsModal.hide();

          // Populate form with event data
          document.getElementById('eventName').value = data.event.event_name;
          document.getElementById('location').value = data.event.location;
          document.getElementById('startDateTime').value = data.event.start.slice(0, 16); // Remove seconds
          document.getElementById('endDateTime').value = data.event.end.slice(0, 16);
          document.getElementById('eventType').value = data.event.event_type_id;

          // Add hidden event_id field if it doesn't exist
          let eventIdInput = document.getElementById('eventId');
          if (!eventIdInput) {
              eventIdInput = document.createElement('input');
              eventIdInput.type = 'hidden';
              eventIdInput.id = 'eventId';
              eventIdInput.name = 'event_id';
              document.getElementById('createEventForm').appendChild(eventIdInput);
          }
          eventIdInput.value = eventId;

          // Clear existing participants and add current ones
          const participantsList = document.getElementById('participantsList');
          participantsList.innerHTML = '';
          data.participants.forEach((participant, index) => {
              if (index === 0) {
                  // First participant slot
                  participantsList.innerHTML = `
                      <div class="participant-entry row mb-2">
                          <div class="col-md-8">
                              <input type="email" class="form-control bg-dark text-white participant-email" 
                                  value="${participant.email}" name="participants[0][email]" required>
                          </div>
                          <div class="col-md-4">
                              <select class="form-select bg-dark text-white" name="participants[0][role]" required>
                                  <option value="professor" ${participant.role_in_event === 'professor' ? 'selected' : ''}>Professor</option>
                                  <option value="tutor" ${participant.role_in_event === 'tutor' ? 'selected' : ''}>Tutor</option>
                                  <option value="tutee" ${participant.role_in_event === 'tutee' ? 'selected' : ''}>Tutee</option>
                              </select>
                          </div>
                      </div>
                  `;
              } else {
                  // Additional participants
                  const newEntry = document.createElement('div');
                  newEntry.className = 'participant-entry row mb-2';
                  newEntry.innerHTML = `
                      <div class="col-md-8">
                          <input type="email" class="form-control bg-dark text-white participant-email" 
                              value="${participant.email}" name="participants[${index}][email]" required>
                      </div>
                      <div class="col-md-3">
                          <select class="form-select bg-dark text-white" name="participants[${index}][role]" required>
                              <option value="professor" ${participant.role_in_event === 'professor' ? 'selected' : ''}>Professor</option>
                              <option value="tutor" ${participant.role_in_event === 'tutor' ? 'selected' : ''}>Tutor</option>
                              <option value="tutee" ${participant.role_in_event === 'tutee' ? 'selected' : ''}>Tutee</option>
                          </select>
                      </div>
                      <div class="col-md-1">
                          <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">×</button>
                      </div>
                  `;
                  participantsList.appendChild(newEntry);
              }
          });

          // Show create/edit modal with populated data
          const modal = new bootstrap.Modal(document.getElementById('createEventModal'));
          modal.show();
      }
  } catch (error) {
      console.error('Error loading event for editing:', error);
      alert('Failed to load event details. Please try again.');
  }
}

// Function to delete event
async function deleteEvent(eventId) {
  if (!confirm('Are you sure you want to delete this event?')) {
      return;
  }

  try {
      const response = await fetch('/jaywing-academy/src/data_src/api/events/delete_event.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ event_id: eventId })
      });
      
      if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
          // Close the details modal first
          const detailsModal = bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal'));
          if (detailsModal) {
              detailsModal.hide();
          }
          
          // Then refetch events
          if (calendar) {
              calendar.refetchEvents();
          } else {
              console.error('Calendar not initialized');
              location.reload(); // Fallback if calendar isn't accessible
          }
      } else {
          throw new Error(data.message || 'Failed to delete event');
      }
  } catch (error) {
      console.error('Error:', error);
      alert('Error deleting event: ' + (error.message || 'Unknown error occurred'));
  }
}


async function showEventDetails(event) {
  try {
      const response = await fetch(`/jaywing-academy/src/data_src/api/events/get_event_details.php?event_id=${event.id}`);
      const data = await response.json();
      
      if (data.success) {
          document.getElementById('detailsEventTitle').textContent = data.event.event_name;
          document.getElementById('detailsEventLocation').textContent = data.event.location;
          document.getElementById('detailsEventTime').textContent = 
              `${new Date(data.event.start).toLocaleString()} - ${new Date(data.event.end).toLocaleString()}`;
          document.getElementById('detailsEventType').textContent = data.event.type_name;
          document.getElementById('detailsEventWings').textContent = data.event.wings;

          // Clear and populate participants table
          const tbody = document.getElementById('participantsTableBody');
          tbody.innerHTML = '';
          data.participants.forEach(participant => {
              tbody.innerHTML += `
                  <tr>
                      <td>${participant.username}</td>
                      <td>${participant.role_in_event}</td>
                  </tr>
              `;
          });

          // Show edit/delete buttons if user is creator
          const actionsDiv = document.getElementById('eventActions');
          actionsDiv.innerHTML = '';
          if (data.event.creator_id === data.current_user_id) {
              actionsDiv.innerHTML = `
                  <button class="btn btn-primary me-2" onclick="editEvent(${event.id})">
                      Edit Event
                  </button>
                  <button class="btn btn-danger" onclick="deleteEvent(${event.id})">
                      Delete Event
                  </button>
              `;
          }

          const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
          modal.show();
      }
  } catch (error) {
      console.error('Error fetching event details:', error);
      alert('Failed to load event details. Please try again.');
  }
}


// Function to add new participant field
let participantCounter = 1;
function addParticipantField() {
  const participantsList = document.getElementById('participantsList');
  if (!participantsList) return;

  const newEntry = document.createElement('div');
  newEntry.className = 'participant-entry row mb-2';
  newEntry.innerHTML = `
      <div class="col-md-8">
          <input type="email" class="form-control bg-dark text-white participant-email" 
                 placeholder="Participant Email" name="participants[${participantCounter}][email]">
      </div>
      <div class="col-md-3">
          <select class="form-select bg-dark text-white" name="participants[${participantCounter}][role]">
              <option value="professor">Professor</option>
              <option value="tutor">Tutor</option>
              <option value="tutee">Tutee</option>
          </select>
      </div>
      <div class="col-md-1">
          <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">×</button>
      </div>
  `;
  participantsList.appendChild(newEntry);
  participantCounter++;
}

