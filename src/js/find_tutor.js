document.addEventListener('DOMContentLoaded', function() {
    let classes = [];
    let visibleClasses = 5;
    let activeCollapse = null;

    // Fetch classes from PHP
    fetch('../data_src/api/tutor/read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                classes = data.classes.map(classItem => ({
                    title: `${classItem.className} (${classItem.courseCode})`,
                    details: `
                        <p>${classItem.classDescription}</p>
                        <h6>Tutors:</h6>
                        <ul>
                            ${classItem.tutors.length > 0 
                                ? classItem.tutors.map(tutor => 
                                    `<li>${tutor.firstName} ${tutor.lastName} (${tutor.email})</li>`
                                ).join('') 
                                : '<li>No tutors assigned</li>'}
                        </ul>
                    `
                }));
                renderClasses();
            } else {
                throw new Error(data.message || 'Failed to fetch classes');
            }
        })
        .catch(error => {
            console.error('Error fetching classes:', error);
            document.getElementById('classesTable').innerHTML = `
                <div class="alert alert-danger">
                    Error loading classes: ${error.message}
                </div>
            `;
        });

    function renderClasses(classesToRender = classes) {
        const table = document.getElementById('classesTable');
        table.innerHTML = '';

        classesToRender.slice(0, visibleClasses).forEach((classItem, index) => {
            const card = document.createElement('div');
            card.className = 'card mb-2';
            
            card.innerHTML = `
                <div class="card-header" style="cursor: pointer;" data-index="${index}">
                    ${classItem.title}
                </div>
                <div id="collapse${index}" class="collapse">
                    <div class="card-body">
                        ${classItem.details}
                    </div>
                </div>
            `;

            // Add click event listener to the card header
            const header = card.querySelector('.card-header');
            header.addEventListener('click', function() {
                const collapseId = `collapse${this.dataset.index}`;
                const collapseElement = document.getElementById(collapseId);
                
                // If there's an active collapse and it's not the current one, close it
                if (activeCollapse && activeCollapse !== collapseElement) {
                    activeCollapse.classList.remove('show');
                }
                
                // Toggle the current collapse
                collapseElement.classList.toggle('show');
                
                // Update the active collapse reference
                activeCollapse = collapseElement.classList.contains('show') ? collapseElement : null;
            });

            table.appendChild(card);
        });

        // Update show more button visibility
        const showMoreButton = document.getElementById("showMoreButton");
        showMoreButton.style.display = visibleClasses >= classesToRender.length ? "none" : "block";
    }

    window.showMoreClasses = function() {
        visibleClasses += 5;
        renderClasses();
    }

    window.filterClasses = function() {
        const query = document.getElementById('searchBar').value.toLowerCase();
        const filteredClasses = classes.filter(classItem => 
            classItem.title.toLowerCase().includes(query)
        );
        activeCollapse = null; // Reset active collapse when filtering
        renderClasses(filteredClasses);
    }
});