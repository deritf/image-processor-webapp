const icons = ["❤️", "🔥", "🚀", "💡"];
let currentIcon = 0;

const iconElement = document.getElementById("changing-icon");

function changeIcon() {
  iconElement.classList.add("fade-out");

  setTimeout(() => {
    currentIcon = (currentIcon + 1) % icons.length;
    iconElement.textContent = icons[currentIcon];

    iconElement.classList.remove("fade-out");
  }, 500);
}

setInterval(changeIcon, 5000);
