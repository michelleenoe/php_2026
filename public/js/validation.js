document.addEventListener("click", (e) => {
  const dialog = e.target.closest(".x-dialog");
  if (!dialog) return;
  if (!e.target.classList.contains("x-dialog__overlay")) return;

  if (dialog.id === "signupDialog" && dialog.classList.contains("active")) {
    e.preventDefault();
    e.stopPropagation();
    return;
  }

  dialog.classList.remove("active");
});

function clearState(input) {
  input.classList.remove("x-error", "x-valid");
}

function validateInput(input) {
  const rule = input.dataset.rule;
  if (!rule) return true;

  const re = new RegExp(rule);
  let ok = re.test(input.value);

  input.classList.toggle("x-valid", ok);
  input.classList.toggle("x-error", !ok);

  if (ok && input.dataset.match) {
    const form = input.closest("form");
    const target = form?.querySelector("#" + input.dataset.match);
    if (target && input.value !== target.value) {
      ok = false;
      input.classList.remove("x-valid");
      input.classList.add("x-error");
    }
  }

  return ok;
}

function validate(form) {
  let valid = true;
  let mismatchedField = null;

  form.querySelectorAll("[data-rule]").forEach((input) => {
    const ok = validateInput(input);
    if (!ok) {
      valid = false;

      if (input.dataset.match && !mismatchedField) {
        mismatchedField = input;
      }
    }
  });

  form._mismatchedField = mismatchedField;
  return valid;
}

function bindForm(form) {
  if (form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", (e) => {
    form.dataset.validationActive = "true";

    if (!validate(form)) {
      e.preventDefault();
      e.stopPropagation();

      let invalid = form._mismatchedField || form.querySelector("[data-rule].x-error") || form.querySelector("[data-rule]");

      let msg = invalid?.dataset.matchError || invalid?.dataset.error || "Please fix the highlighted fields.";

      window.showToast(msg, "error");
      return false;
    }
  });

  form.querySelectorAll("[data-rule]").forEach((input) => {
    input.addEventListener("input", () => {
      clearState(input);

      if (input.dataset.match) {
        validateInput(input);
      } else {
        const matchField = form.querySelector(`[data-match="${input.id}"]`);
        if (matchField && matchField.value) {
          validateInput(matchField);
        }
      }
    });
  });
}

function activateValidation() {
  const forms = new Set();

  document.querySelectorAll("[data-rule]").forEach((input) => {
    const form = input.closest("form");
    if (form) forms.add(form);
  });

  forms.forEach((form) => bindForm(form));

  document.querySelectorAll("input.x-error:not([data-rule])").forEach((input) => {
    if (input.dataset.bound === "true") return;
    input.dataset.bound = "true";

    input.addEventListener("input", () => clearState(input));
  });
}

activateValidation();

document.addEventListener("DOMContentLoaded", activateValidation);
document.addEventListener("mix:page-updated", activateValidation);
setTimeout(activateValidation, 500);
