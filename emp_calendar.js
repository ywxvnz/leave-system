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

const generateCalendar = (month, year) => {
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

    for (let i = 0; i < first_day + days_of_month[month]; i++) {
        let day = document.createElement("div");
        if (i >= first_day) {
            let dateNum = i - first_day + 1;
            day.innerHTML = dateNum;

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
        generateCalendar(currentMonth.value, currentYear.value);
        month_list.classList.toggle('show');
    };
});

let currentDate = new Date();
let currentMonth = { value: currentDate.getMonth() };
let currentYear = { value: currentDate.getFullYear() };

generateCalendar(currentMonth.value, currentYear.value);

document.querySelector("#pre-year").onclick = () => {
    --currentYear.value;
    generateCalendar(currentMonth.value, currentYear.value);
};
document.querySelector("#next-year").onclick = () => {
    ++currentYear.value;
    generateCalendar(currentMonth.value, currentYear.value);
};

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

const style = document.createElement("style");
style.innerHTML = `
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