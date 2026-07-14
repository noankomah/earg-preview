document.addEventListener("DOMContentLoaded", function () {
  const editorElements = document.querySelectorAll("[data-rich-text-editor]");

  editorElements.forEach(function (editorElement) {
    const targetId = editorElement.dataset.target;
    const hiddenInput = document.getElementById(targetId);

    if (!hiddenInput || typeof Quill === "undefined") {
      return;
    }

    const quill = new Quill(editorElement, {
      theme: "snow",
      placeholder:
        editorElement.dataset.placeholder || "Enter formatted content...",
      modules: {
        toolbar: [
          [{ header: [2, 3, false] }],
          ["bold", "italic"],
          [{ list: "ordered" }, { list: "bullet" }],
          ["link"],
          ["clean"]
        ]
      },
      formats: [
        "header",
        "bold",
        "italic",
        "list",
        "link"
      ]
    });

    if (hiddenInput.value.trim() !== "") {
      quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
    }

    const form = editorElement.closest("form");

    if (form) {
      form.addEventListener("submit", function () {
        const html = quill.root.innerHTML.trim();

        hiddenInput.value =
          html === "<p><br></p>" ? "" : html;
      });
    }
  });
});
