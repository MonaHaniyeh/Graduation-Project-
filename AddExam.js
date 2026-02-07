// AddExam.js
toast = document.getElementById(toastMessage);

document.addEventListener('DOMContentLoaded', function () {
    // Load all data
    loadNonConflictingCourses();
    loadDropdownData('courses', 'course1');
    loadDropdownData('courses', 'course2');
    loadDropdownData('instructors', 'instructor1');
    loadDropdownData('instructors', 'instructor2');
    loadDropdownData('classrooms', 'classroom1');
    loadDropdownData('classrooms', 'classroom2');

    // Form submission handler
    document.getElementById('examForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Validate classrooms are different
        const classroom1 = document.getElementById('classroom1').value;
        const classroom2 = document.getElementById('classroom2').value;

        if (classroom1 === classroom2) {
            showToastMessage('Both exams cannot be in the same classroom', true);
            return;
        }

        // Validate time
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;

        if (startTime >= endTime) {
            showToastMessage('Start time must be before end time', true)
            return;
        }

        // Submit form
        submitExamForm();
    });

    // Add animation to cards on load
    const cards = document.querySelectorAll('.card, .exam-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
});

function loadNonConflictingCourses() {
    fetch('load_data.php?type=non_conflicting')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('nonConflictingTable');
            tableBody.innerHTML = '';

            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${item.id}</td>
                    <td>${item.course1}</td>
                    <td>${item.course2}</td>
                    
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error loading non-conflicting courses:', error));
}

function loadDropdownData(type, elementId) {
    fetch(`load_data.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById(elementId);
            select.innerHTML = '<option value="">Select ' + type.charAt(0).toUpperCase() + type.slice(1) + '...</option>';

            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item[type === 'courses' ? 'CourseID' :
                    type === 'instructors' ? 'InstructorID' : 'ClassRoomID'];
                option.textContent = item[type === 'courses' ? 'CourseName' :
                    type === 'instructors' ? 'InstructorFullName' :
                        'ClassRoomNumber'] +
                    (type === 'classrooms' ? ` (${item.RoomType})` : '');
                select.appendChild(option);
            });
        })
        .catch(error => console.error(`Error loading ${type}:`, error));
}

function submitExamForm() {
    const formData = new FormData(document.getElementById('examForm'));

    fetch('process_exam.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToastMessage(data.message, false)
                document.getElementById('examForm').reset();
            } else {
                showToastMessage(data.message, true)
            }
        })
        .catch(error => {
            showToastMessage('An error occurred while submitting the form', true)
            console.error('Error:', error);
        });
}
function showToastMessage(message, isError = false) {
    const toast = document.getElementById('toastMessage');
    const toastText = document.getElementById('toastText');

    // Clear any existing timeout
    if (toast.timeoutId) {
        clearTimeout(toast.timeoutId);
    }

    // Set message and color
    toastText.textContent = message;
    toast.style.backgroundColor = isError ? '#f44336' : '#4CAF50';

    // Show message
    toast.removeAttribute('hidden');
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);

    // Hide after 3 seconds
    toast.timeoutId = setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.setAttribute('hidden', 'true');
        }, 500);
    }, 3000);
}

// تعديل دالة submitExamForm
function submitExamForm() {

    const formData = new FormData(document.getElementById('examForm'));
    const exam2Enabled = document.getElementById('exam2Section').style.display !== 'none';
    const hasExam2 = exam2Enabled &&
        document.getElementById('course2').value &&
        document.getElementById('instructor2').value &&
        document.getElementById('classroom2').value;

    // إذا كان Exam 2 معطلاً أو غير مكتمل، احذف قيمه من formData
    if (!hasExam2) {
        formData.delete('course2');
        formData.delete('instructor2');
        formData.delete('classroom2');
    }

    fetch('process_exam.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToastMessage(data.message, false);
                document.getElementById('examForm').reset();
                // إعادة تعيين Exam 2 إذا كان مفعلاً
                if (exam2Enabled) {
                    document.getElementById('exam2Section').style.display = 'none';
                    document.getElementById('toggleExam2').innerHTML = '<i class="fas fa-plus"></i> Add Second Exam';
                }
            } else {
                showToastMessage(data.message, true);
            }
        })
        .catch(error => {
            showToastMessage('An error occurred while submitting the form', true);
            console.error('Error:', error);
        });

}
// Add toggle functionality for Exam 2
const toggleExam2Btn = document.getElementById('toggleExam2');
const exam2Section = document.getElementById('exam2Section');

toggleExam2Btn.addEventListener('click', function () {
    if (exam2Section.style.display === 'none') {
        // Show Exam 2 section
        exam2Section.style.display = 'block';
        toggleExam2Btn.innerHTML = '<i class="fas fa-minus"></i> Remove Second Exam';
        toggleExam2Btn.classList.remove('btn-secondary');
        toggleExam2Btn.classList.add('btn-primary');

        // Make fields not required (optional)
        document.getElementById('course2').removeAttribute('required');
        document.getElementById('instructor2').removeAttribute('required');
        document.getElementById('classroom2').removeAttribute('required');
    } else {
        // Hide Exam 2 section
        exam2Section.style.display = 'none';
        toggleExam2Btn.innerHTML = '<i class="fas fa-plus"></i> Add Second Exam';
        toggleExam2Btn.classList.remove('btn-primary');
        toggleExam2Btn.classList.add('btn-secondary');

        // Clear values
        document.getElementById('course2').value = '';
        document.getElementById('instructor2').value = '';
        document.getElementById('classroom2').value = '';
    }
});
