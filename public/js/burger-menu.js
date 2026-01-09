export function burgerMenu() {
  const burger = document.querySelector(".burger");
  const nav = document.querySelector("nav");
  const main = document.querySelector("main");
  const aside = document.querySelector("aside");
  if (!burger || !nav || !main || !aside) return;


  burger.addEventListener("click", () => {
    nav.classList.toggle("active");
    burger.classList.toggle("open");
    main.classList.toggle("blur-overlay");
    aside.classList.toggle("blur-overlay");
  });


  window.addEventListener("resize", () => {
    if (nav.classList.contains("active")) {
      nav.classList.remove("active");
      burger.classList.remove("open");
      main.classList.remove("blur-overlay");
      aside.classList.remove("blur-overlay");
    }
  });
}
