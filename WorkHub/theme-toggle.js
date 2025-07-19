document.addEventListener("DOMContentLoaded", () => {
  const currentTheme = localStorage.getItem("theme") || "light";
  document.documentElement.setAttribute("data-theme", currentTheme);

  const toggleBtn = document.createElement("button");
  toggleBtn.innerText = currentTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
  toggleBtn.style.position = "absolute";
  toggleBtn.style.top = "10px";
  toggleBtn.style.right = "10px";
  document.body.appendChild(toggleBtn);

  toggleBtn.onclick = () => {
    const newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    toggleBtn.innerText = newTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
  };
});
