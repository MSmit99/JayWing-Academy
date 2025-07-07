function loadEnrollments() {
    fetch('../data_src/api/classes/get_classes.php')
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Error loading enrollments:', result.message);
                return;
            }

            const enrollments = result.data; // Access the data array from response
            const tbody = document.getElementById('enrollmentsTable');
            tbody.innerHTML = '';
            
            if (Array.isArray(enrollments)) {
                enrollments.forEach(enrollment => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${enrollment.className}</td>
                        <td>${enrollment.firstName} ${enrollment.lastName} (${enrollment.username})</td>
                        <td>${enrollment.roleOfClass}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editEnrollment(${JSON.stringify(enrollment)})">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteEnrollment(${enrollment.enrollment_id})">
                                Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function editEnrollment(enrollment) {
    document.getElementById('edit_enrollment_id').value = enrollment.enrollment_id;
    document.getElementById('edit_class_id').value = enrollment.class_id;
    document.getElementById('edit_user_id').value = enrollment.user_id;
    document.getElementById('edit_roleOfClass').value = enrollment.roleOfClass;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteEnrollment(enrollmentId) {
    if (confirm('Are you sure you want to delete this enrollment?')) {
        fetch('../data_src/api/classes/delete_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enrollment_id: enrollmentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadEnrollments();
            }
        });
    }
}

function loadClasses() {
    fetch('../data_src/api/classes/get_all_classes.php')
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Error loading classes:', result.message);
                return;
            }
            
            const classes = result.data; // Access the data array from response
            const tbody = document.getElementById('classesTable');
            tbody.innerHTML = '';
            
            if (Array.isArray(classes)) {
                classes.forEach(classItem => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${classItem.className}</td>
                        <td>${classItem.courseCode || ''}</td>
                        <td>${classItem.classDescription || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editClass(${JSON.stringify(classItem)})">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteClass(${classItem.class_id})">
                                Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            // Reload enrollment dropdowns
            reloadClassDropdowns();
            loadEnrollments();
        })
        .catch(error => console.error('Error:', error));
}

function deleteClass(classId) {
    if (confirm('Are you sure? This will also delete all enrollments for this class.')) {
        fetch('../data_src/api/classes/delete_class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ class_id: classId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset selected class text
                document.getElementById('selectedClassText').textContent = 'Select Class';
                document.getElementById('class_id').value = '';
                
                loadClasses();
                reloadClassDropdowns();
                loadEnrollments();
            }
        });
    }
}

function initializeSearchableDropdowns() {
    // Stop dropdowns from closing when clicking inside
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', e => e.stopPropagation());
    });

    // Class search functionality
    document.getElementById('classSearchInput').addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        document.querySelectorAll('.class-list .dropdown-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText) ? 'block' : 'none';
        });
    });

    // User search functionality
    document.getElementById('userSearchInput').addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        document.querySelectorAll('.user-list .dropdown-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText) ? 'block' : 'none';
        });
    });

    // Handle item selection
    document.querySelectorAll('.class-list .dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.textContent;
            document.getElementById('class_id').value = value;
            document.getElementById('selectedClassText').textContent = text;
        });
    });

    document.querySelectorAll('.user-list .dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.textContent;
            document.getElementById('user_id').value = value;
            document.getElementById('selectedUserText').textContent = text;
        });
    });
}

function reloadClassDropdowns() {
    fetch('../data_src/api/classes/get_all_classes.php')
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Error loading classes:', result.message);
                return;
            }

            const classes = result.data;
            
            // Update dropdown menus
            const classDropdown = document.querySelector('.class-list');
            if (classDropdown) {
                classDropdown.innerHTML = '';
                if (Array.isArray(classes)) {
                    classes.forEach(classItem => {
                        const dropdownItem = document.createElement('div');
                        dropdownItem.className = 'dropdown-item text-white';
                        dropdownItem.dataset.value = classItem.class_id;
                        dropdownItem.textContent = `${classItem.className} (${classItem.courseCode || ''})`;
                        classDropdown.appendChild(dropdownItem);
                    });
                }
            }

            // Update edit form select
            const editClassSelect = document.getElementById('edit_class_id');
            if (editClassSelect) {
                editClassSelect.innerHTML = '';
                if (Array.isArray(classes)) {
                    classes.forEach(classItem => {
                        const option = document.createElement('option');
                        option.value = classItem.class_id;
                        option.textContent = `${classItem.className} (${classItem.courseCode || ''})`;
                        editClassSelect.appendChild(option);
                    });
                }
            }

            // Reinitialize search functionality
            initializeSearchableDropdowns();
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadClasses();
    initializeSearchableDropdowns();

    document.getElementById('classForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            className: document.getElementById('className').value,
            courseCode: document.getElementById('courseCode').value,
            classDescription: document.getElementById('classDescription').value
        };
        
        fetch('../data_src/api/classes/create_class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadClasses();
                reloadClassDropdowns();
                this.reset();
                // Reset selected class text
                document.getElementById('selectedClassText').textContent = 'Select Class';
            }
        });
    });

    loadEnrollments();
    
    // Add Enrollment Form Handler
    document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            class_id: document.getElementById('class_id').value,
            user_id: document.getElementById('user_id').value,
            roleOfClass: document.getElementById('roleOfClass').value
        };
        
        fetch('../data_src/api/classes/create_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadEnrollments();
                this.reset();
            }
        });
    });
    
    // Edit Form Handler
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            enrollment_id: document.getElementById('edit_enrollment_id').value,
            class_id: document.getElementById('edit_class_id').value,
            user_id: document.getElementById('edit_user_id').value,
            roleOfClass: document.getElementById('edit_roleOfClass').value
        };
        
        fetch('../data_src/api/classes/update_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadEnrollments();
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            }
        });
    });
});

