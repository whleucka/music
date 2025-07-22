document.addEventListener("DOMContentLoaded", function(event) {
  const links = document.querySelectorAll('#sidebar-links .link');
  links.forEach((link) => {
    let path = window.location.href.split('?')[0]
    if (link.href === path) {
        link.classList.add("active");
    }
  });
  sidebarVisibility();
});

const sidebarLinkActive = (e) => {
  const links = document.querySelectorAll('#sidebar-links .link');
  if (links.length) {
    links.forEach((link) => {
      link.classList.remove('active');
    });
    e.preventDefault();
    e.currentTarget.classList.add('active');
    console.log(e.currentTarget)
  }
}

const sidebarToggle = () => {
  const sidebar = document.querySelector("#nav-sidebar");
  sidebar.classList.toggle("hide");
}

function sidebarVisibility() {
  const sidebar = document.querySelector("#nav-sidebar");
  if (window.innerWidth < 700) {
    sidebar.classList.add('hide');
  } else {
    sidebar.classList.remove('hide');
  }
}
window.addEventListener('resize', sidebarVisibility);
