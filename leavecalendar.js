const isLeapYear = (year) => {
    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
};

const getFebDays = (year) => (isLeapYear(year) ? 29 : 28);

let calendar = document.querySelector(".calendar");
const month_names = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

let month_picker = document.querySelector("#month-picker");
const dayTextFormate = document.querySelector(".day-text-formate");
const timeFormate = document.querySelector(".time-formate");
const dateFormate = document.querySelector(".date-formate");

let month_list = document.createElement("div");
month_list.classList.add("month-list");
calendar.appendChild(month_list);

month_picker.onclick = () => {
    month_list.classList.toggle('show');
    dayTextFormate.classList.remove('hidetime');
    timeFormate.classList.remove('hideTime');
    dateFormate.classList.remove('hideTime');
};

const leaveDetailsDisplay = document.createElement("div");
leaveDetailsDisplay.classList.add("leave-details");
document.body.appendChild(leaveDetailsDisplay);

const generateCalendar = (month, year, leaveRequests = []) => {
    let calendar_days = document.querySelector(".calendar-days");
    if (!calendar_days) return;
    
    calendar_days.innerHTML = "";
    let calendar_header_year = document.querySelector("#year");

    let days_of_month = [
        31, getFebDays(year), 31, 30, 31, 30, 31,
        31, 30, 31, 30, 31
    ];
    let currentDate = new Date();

    month_picker.innerHTML = month_names[month];
    calendar_header_year.innerHTML = year;

    let first_day = new Date(year, month).getDay();
    let selectedDateDisplay = document.querySelector(".selected-date-display");

    let leaveMap = new Map();

    leaveRequests.forEach(request => {
        let start = new Date(request.start_date);
        let end = new Date(request.end_date);

        while (start <= end) {
            let leaveDate = start.toISOString().split("T")[0];

            if (!leaveMap.has(leaveDate)) {
                leaveMap.set(leaveDate, []);
            }
            leaveMap.get(leaveDate).push(request);

            start.setDate(start.getDate() + 1);
        }
    });

    for (let i = 0; i < first_day + days_of_month[month]; i++) {
        let day = document.createElement("div");
        if (i >= first_day) {
            let dateNum = i - first_day + 1;
            day.innerHTML = dateNum;

            let dayDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(dateNum).padStart(2, "0")}`;
            day.setAttribute("data-date", dayDate);

            if (leaveMap.has(dayDate)) {
                let leaveInfo = leaveMap.get(dayDate);
                day.style.backgroundColor = "#4CAF50";
                day.title = `Leave Approved: ${leaveInfo[0].start_date} - ${leaveInfo[0].end_date}`;
                day.classList.add("leave-day");
            }

            if (
                dateNum === currentDate.getDate() &&
                year === currentDate.getFullYear() &&
                month === currentDate.getMonth()
            ) {
                day.classList.add("current-date");
            }

            day.onclick = () => {
                document.querySelectorAll(".calendar-days div").forEach(d => d.classList.remove("selected-date"));
                day.classList.add("selected-date");

                selectedDateDisplay.textContent = `Selected Date: ${dateNum} ${month_names[month]} ${year}`;

                if (leaveMap.has(dayDate)) {
                    let leaveRequestsForDate = leaveMap.get(dayDate);
                    leaveDetailsDisplay.innerHTML = `
                        <div class="leave-info">
                            <p><strong>Leave Details:</strong></p>
                            ${leaveRequestsForDate.map(leaveInfo => `
                                <p><strong>Name:</strong> ${leaveInfo.name || "N/A"}</p>
                                <p><strong>Start Date:</strong> ${leaveInfo.start_date}</p>
                                <p><strong>End Date:</strong> ${leaveInfo.end_date}</p>
                                <p><strong>Status:</strong> Approved</p>
                                <hr />
                            `).join('')}
                        </div>
                    `;
                } else {
                    leaveDetailsDisplay.innerHTML = `
                        <div class="leave-info">
                            <p><strong>Selected Date:</strong> ${dateNum} ${month_names[month]} ${year}</p>
                            <p>No leave records for this date.</p>
                        </div>
                    `;
                }
                leaveDetailsDisplay.style.display = "block";
            };
        }
        calendar_days.appendChild(day);
    }
};

month_names.forEach((e, index) => {
    let month = document.createElement("div");
    month.innerHTML = `<div>${e}</div>`;
  
    month_list.append(month);
    month.onclick = () => {
        currentMonth.value = index;
        generateCalendar(currentMonth.value, currentYear.value, leaveRequests);
        month_list.classList.toggle('show');
    };
});

let currentDate = new Date();
let currentMonth = { value: currentDate.getMonth() };
let currentYear = { value: currentDate.getFullYear() };

let leaveRequests = [];
fetch("fetch_leave_requests.php")
    .then(response => response.json())
    .then(data => {
        leaveRequests = data;
        generateCalendar(currentMonth.value, currentYear.value, leaveRequests);
    })
    .catch(error => console.error("Error fetching leave requests:", error));

document.querySelector("#pre-year").onclick = () => {
    --currentYear.value;
    generateCalendar(currentMonth.value, currentYear.value, leaveRequests);
};
document.querySelector("#next-year").onclick = () => {
    ++currentYear.value;
    generateCalendar(currentMonth.value, currentYear.value, leaveRequests);
};

// **REAL-TIME DATE & TIME UPDATE**
const todayShowTime = document.querySelector(".time-formate");
const todayShowDate = document.querySelector(".date-formate");

function updateDateTime() {
    const now = new Date();

    todayShowTime.textContent = now.toLocaleTimeString("en-US", { hour12: false });
    todayShowDate.textContent = now.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        weekday: "long"
    });
}

setInterval(updateDateTime, 1000);
updateDateTime();

// **CSS Styles**
const style = document.createElement("style");
style.innerHTML = `
.leave-details {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: white;
    padding: 10px;
    border: 1px solid #ddd;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
    border-radius: 5px;
    display: none;
}

.leave-info {
    font-size: 14px;
    color: #333;
}

.selected-date {
    border: 2px solid #FF9800 !important;
}

.month-list {
    position: absolute;
    top: 0;
    left: 65%; /* Positions it to the right of the calendar */
    margin-left: 20px; /* Adds spacing from the calendar */
    background: white;
    display: none;
    padding: 3px;
    width: 150px; /* Adjust width if needed */
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    border: 1px solid #ddd;
    border-radius: 5px;
}

.month-list.show {
    display: block;
}

.month-list div {
    padding: 3px;
    text-align: center;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
    transition: background 0.3s;
}

.month-list div:hover {
    background: #f0f0f0;
}

`;
document.head.appendChild(style);