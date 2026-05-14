var forms = document.querySelectorAll('.needs-validation');

for (var i = 0; i < forms.length; i++) {
    forms[i].addEventListener('submit', function (event) {
        event.preventDefault();

        var form = event.target;
        var messageElement = form.querySelector('.form-message');
        var email = form.querySelector('input[name="email"]');
        var password = form.querySelector('input[name="password"]');
        var confirmPassword = form.querySelector('input[name="confirm_password"]');

        if (email && email.value.indexOf('yic.edu.sa') === -1) {
            if (messageElement) {
                messageElement.textContent = 'Please enter a valid YIC email.';
                messageElement.style.color = '#c62828';
            }
            return;
        }

        if (password && confirmPassword && password.value !== confirmPassword.value) {
            if (messageElement) {
                messageElement.textContent = 'Passwords do not match.';
                messageElement.style.color = '#c62828';
            }
            return;
        }

        if (messageElement) {
            if (form.getAttribute('data-success-message')) {
                messageElement.textContent = form.getAttribute('data-success-message');
            } else {
                messageElement.textContent = 'Form submitted successfully.';
            }
            messageElement.style.color = '#0b7a43';
        }

        if (form.getAttribute('data-success-target')) {
            setTimeout(function () {
                window.location.href = form.getAttribute('data-success-target');
            }, 500);
        }
    });
}

var tabButtons = document.querySelectorAll('.tab-button');

for (var j = 0; j < tabButtons.length; j++) {
    tabButtons[j].addEventListener('click', function () {
        var targetId = this.getAttribute('data-tab-target');
        var allButtons = document.querySelectorAll('.tab-button');
        var allPanels = document.querySelectorAll('.tab-panel');
        var k;

        for (k = 0; k < allButtons.length; k++) {
            allButtons[k].classList.remove('is-active');
        }

        for (k = 0; k < allPanels.length; k++) {
            allPanels[k].classList.remove('is-active');
        }

        this.classList.add('is-active');
        document.getElementById(targetId).classList.add('is-active');
    });
}

var slotFilterForm = document.getElementById('slot-filter-form');

if (slotFilterForm) {
    slotFilterForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var service = document.getElementById('service-filter').value;
        var staff = document.getElementById('staff-filter').value;
        var date = document.getElementById('date-filter').value;
        var rows = document.querySelectorAll('#slot-table-body tr');
        var visibleCount = 0;
        var i;

        for (i = 0; i < rows.length; i++) {
            var serviceMatch = service === 'all' || rows[i].getAttribute('data-service') === service;
            var staffMatch = staff === 'all' || rows[i].getAttribute('data-staff') === staff;
            var dateMatch = date === '' || rows[i].getAttribute('data-date') === date;

            if (serviceMatch && staffMatch && dateMatch) {
                rows[i].classList.remove('hidden-row');
                visibleCount++;
            } else {
                rows[i].classList.add('hidden-row');
            }
        }

        var feedback = document.getElementById('slot-feedback');
        if (feedback) {
            if (visibleCount > 0) {
                feedback.textContent = visibleCount + ' slot(s) found.';
            } else {
                feedback.textContent = 'No slots found.';
            }
        }
    });
}

var bookButtons = document.querySelectorAll('.book-btn');

for (var b = 0; b < bookButtons.length; b++) {
    bookButtons[b].addEventListener('click', function () {
        var feedback = document.getElementById('slot-feedback');
        if (feedback) {
            feedback.textContent = 'Appointment booked successfully.';
            feedback.style.color = '#0b7a43';
        }
    });
}

var cancelButtons = document.querySelectorAll('.cancel-btn');

for (var c = 0; c < cancelButtons.length; c++) {
    cancelButtons[c].addEventListener('click', function () {
        var answer = confirm('Cancel this appointment?');

        if (answer) {
            this.parentNode.parentNode.remove();
        }
    });
}

var deleteButtons = document.querySelectorAll('.delete-row-btn');

for (var d = 0; d < deleteButtons.length; d++) {
    deleteButtons[d].addEventListener('click', function () {
        var answer = confirm('Are you sure?');

        if (answer) {
            this.parentNode.parentNode.remove();
        }
    });
}

var confirmButtons = document.querySelectorAll('.confirm-btn');

for (var f = 0; f < confirmButtons.length; f++) {
    confirmButtons[f].addEventListener('click', function () {
        var row = this.parentNode.parentNode;
        var statusCell = row.cells[5];

        statusCell.innerHTML = '<span class="badge badge-success">Confirmed</span>';
        this.remove();
    });
}