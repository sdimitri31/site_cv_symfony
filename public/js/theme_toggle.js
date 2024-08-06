function initializeThemeToggle() {
    const themeToggleBtn = document.getElementById("theme-toggle");
    const iconLight = document.getElementById("icon-light");
    const iconDark = document.getElementById("icon-dark");
    const currentTheme = localStorage.getItem("theme");

    // Détecter le thème du navigateur
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;

    function setTheme(theme) {
        document.body.setAttribute("data-bs-theme", theme);
        localStorage.setItem("theme", theme);
        updateIcon(theme);
    }

    function updateIcon(theme) {
        iconLight.classList.remove("active");
        iconDark.classList.remove("active");
        if (theme === "light") {
            iconDark.classList.add("active");
        } else {
            iconLight.classList.add("active");
        }
    }

    if (currentTheme) {
        setTheme(currentTheme);
    } else if (prefersDarkScheme) {
        setTheme("dark");
    } else {
        setTheme("light");
    }

    themeToggleBtn.addEventListener("click", function() {
        const currentTheme = document.body.getAttribute("data-bs-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";
        setTheme(newTheme);
    });
}
