// src/utils/components.js

/**
 * A simple loading spinner component.
 * Displays a rotating circle animation.
 */
export const LoadingSpinner = {
  template: `
    <div class="flex justify-center items-center h-full">
      <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500"></div>
    </div>
  `,
};

/**
 * A message display component for showing success or error messages.
 * Props:
 * - type (string): 'success' or 'error' to determine styling.
 * - message (string): The message text to display.
 */
export const Message = {
  props: ["type", "message"],
  template: `
    <div v-if="message" :class="\`border px-4 py-3 rounded relative mb-4 \${type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'}\`" role="alert">
      <strong class="font-bold">{{ type === 'error' ? 'Error!' : 'Success!' }}</strong>
      <span class="block sm:inline ml-2">{{ message }}</span>
    </div>
  `,
};
