/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["templates/**/*.twig", "tw_tws.theme"],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Open Sans", "system-ui", "sans-serif"],
        nevis: ["Nevis", "sans-serif"],
        "nevis-bold": ["Nevis Bold", "sans-serif"],
      },

      colors: {
        gray83: "rgb(83 83 83)",
      },
    },
  },
  plugins: [],
};
