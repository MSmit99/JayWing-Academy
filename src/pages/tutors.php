<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Tutor Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const classes = [
                { title: "Class 1", details: "Details for Class 1..." },
                { title: "Class 2", details: "Details for Class 2..." },
                { title: "Class 3", details: "Details for Class 3..." },
                { title: "Class 4", details: "Details for Class 4..." },
                { title: "Class 5", details: "Details for Class 5..." },
                { title: "Class 6", details: "Details for Class 6..." },
                { title: "Class 7", details: "Details for Class 7..." }
            ];

            let visibleClasses = 5;
            let activeCollapse = null;

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
            }

            function showMoreClasses() {
                visibleClasses += 5;
                if (visibleClasses >= classes.length) {
                    document.getElementById("showMoreButton").style.display = "none";
                }
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

            renderClasses();
        </script>
    </main>
    
    <footer id="footer"></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/global.js"></script>
</body>
</html>