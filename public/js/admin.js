document.addEventListener("DOMContentLoaded", function(event) {
  sidebarLinkActiveLoad();
  sidebarVisibility();
});

const sidebarLinkActiveLoad = () => {
  const links = document.querySelectorAll('#sidebar-links .link');
  links.forEach((link) => {
    link.classList.remove('active');
    let path = window.location.href.split('?')[0]
    console.log(path);
    if (link.href === path) {
        link.classList.add("active");
    }
  });
}

const sidebarLinkActive = (e) => {
  const links = document.querySelectorAll('#sidebar-links .link');
  if (links.length) {
    links.forEach((link) => {
      link.classList.remove('active');
    });
    e.preventDefault();
    e.currentTarget.classList.add('active');
  }
}

function sidebarVisibility() {
  const sidebar = document.querySelector("#nav-sidebar");
  const sidebarToggle = document.querySelector("#sidebar-toggle");
  if (window.innerWidth < 800 && !sidebar.classList.contains("hide")) {
    sidebarToggle.click();
  }
}
window.addEventListener('resize', sidebarVisibility);
