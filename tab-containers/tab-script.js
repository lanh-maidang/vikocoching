// read data from data json
fetch("./tab-data.json")
  .then((res) => res.json())
  .then((data) => {
    const tabsContainer = document.querySelector(".container-tabs");
    const contentsContainer = document.querySelector(".tab-contents");

    // create tab
    data.forEach((item) => {
      const tab = document.createElement("div");
      tab.classList.add("container-tab");
      tab.dataset.tab = item.id;
      fetch(item.icon)
        .then((res) => res.text())
        .then((svgContent) => {
          tab.innerHTML = `
      ${svgContent}
      <span>${item.title}</span>
    `;
        });

      tabsContainer.appendChild(tab);
    });

    // tab event
    tabsContainer.addEventListener("click", (e) => {
      const tabEl = e.target.closest(".container-tab");
      if (!tabEl) return; // outside tab click -> out

      // Active tab
      document
        .querySelectorAll(".container-tab")
        .forEach((t) => t.classList.remove("active"));
      tabEl.classList.add("active");

      // get tab id
      const id = Number(tabEl.dataset.tab);
      renderContents(id);
    });

    // render contents function
    function renderContents(id) {
      contentsContainer.innerHTML = "";

      const tabData = data.find((d) => d.id === id);
      tabData.content.forEach((cItem, index) => {
        const div = document.createElement("div");
        div.classList.add("content-item");

        const titleRow = document.createElement("div");
        titleRow.innerHTML = `
          <span>${cItem.contentTitle}</span>
          <span class="arrow"></span>
        `;
        div.appendChild(titleRow);
        fetch("icons/chevron-down.svg")
          .then((res) => res.text())
          .then((svgContent) => {
            titleRow.querySelector(".arrow").innerHTML = svgContent;
          });
        const desc = document.createElement("div");
        desc.classList.add("description");
        desc.textContent = cItem.description;
        div.appendChild(desc);

        // Toggle
        titleRow.addEventListener("click", () => {
          const isVisible = desc.style.display === "block";
          desc.style.display = isVisible ? "none" : "block";
          div.classList.toggle('open', !isVisible);
          titleRow.querySelector(".arrow").classList.toggle("open", !isVisible);
        });

        contentsContainer.appendChild(div);
      });
    }

    // first tab load
    document.querySelector(".container-tab").click();
  })
  .catch((err) => console.error(err));
