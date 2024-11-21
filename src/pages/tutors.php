<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Tutor Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>

    <main>
        <div class="container mt-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Search classes..." oninput="filterClasses()">
            <div id="classesTable" class="mt-3"></div>
            <button id="showMoreButton" class="btn btn-primary mt-3" onclick="showMoreClasses()">Show More</button>
        </div>

        <script>
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

            function showMoreClasses() {
                visibleClasses += 5;
                renderClasses();
            }

            function filterClasses() {
                const query = document.getElementById('searchBar').value.toLowerCase();
                const filteredClasses = classes.filter(classItem => 
                    classItem.title.toLowerCase().includes(query)
                );
                activeCollapse = null; // Reset active collapse when filtering
                renderClasses(filteredClasses);
            }
        </script>
    </main>
    
    <footer id="footer"></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../js/global.js"></script>
</body>
</html>