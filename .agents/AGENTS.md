# Development Rules for PepperCore Admin

These project-scoped guidelines must be followed by all coding assistants working on this codebase:

## Page Naming Conventions
1. **List Screens:**
   * Always name the listing model page as `[ModelName]List.vue` (e.g., `UserList.vue`).
   * Do not use variations like `UserListPage.vue` or generic `Index.vue`.

2. **Form Screens:**
   * Always use a **single** unified form file for both creating and updating a model.
   * Name this file `[ModelName]Form.vue` (e.g., `UserForm.vue`). Do not split into separate create and update forms.

## Routing and Edit/Create Detection
1. **Unified Form Route:**
   * The Vue Router must configure a unified component mapping for the form route:
     * Creation path: `/model-name/form`
     * Edit path: `/model-name/form/:id`
   * Example: `/user/form` maps to creation mode, and `/user/form/1` maps to editing mode for user ID 1.
2. **State Resolution:**
   * Inside the `[ModelName]Form.vue` component, detect the mode based on the presence of the `:id` parameter (e.g., in `route.params.id` or via router props).
   * If an ID is present, load the existing model data from the backend API to populate the form and submit using a `PUT` request.
   * If no ID is present, render empty fields and submit using a `POST` request.
