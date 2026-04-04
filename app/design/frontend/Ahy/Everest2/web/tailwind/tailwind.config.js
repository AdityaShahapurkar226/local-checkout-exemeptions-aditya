const { spacing } = require("tailwindcss/defaultTheme");

const defaultTheme = require('tailwindcss/defaultTheme');

const colors = require("tailwindcss/colors");

const hyvaModules = require("@hyva-themes/hyva-modules");

const selectorParser = require("postcss-selector-parser");

const plugin = require("tailwindcss/plugin");

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      variants: {
        extend: {
          opacity: ['disabled', 'group-hover', 'group-focus'],
          pointerEvents: ['group-hover']
        },
        colors: {
          primary: { "50": "#eff6ff", "100": "#dbeafe", "200": "#bfdbfe", "300": "#93c5fd", "400": "#60a5fa", "500": "#3b82f6", "600": "#2563eb", "700": "#1d4ed8", "800": "#1e40af", "900": "#1e3a8a" }
        }
      },
      textUnderlineOffset: {
        3: '3px',
        5: '5px',
        6: '6px',
      },
      important: true,
      fontFamily: {
        poppins: ['Poppins', ...defaultTheme.fontFamily.sans],
        'league-gothic': ['League Gothic', ...defaultTheme.fontFamily.sans],
        sans: ["Segoe UI", "Helvetica Neue", "Arial", "sans-serif"],
      },
      screens: {
        sm: "640px",
        // => @media (min-width: 640px) { ... }
        md: "768px",
        // => @media (min-width: 768px) { ... }
        lg: "1024px",
        // => @media (min-width: 1024px) { ... }
        xl: "1280px",
        // => @media (min-width: 1280px) { ... }
        "2xl": "1536px", // => @media (min-width: 1536px) { ... }
      },
      ringOffsetWidth: {
        '3': '3px',
      },
      minWidth: {
        '1/2': '50%',
      },
      aspectRatio: {
        '3/2': '3/2',
        '3/3': '3/3',
        '5/4': '5/4',
        '4/5': '4/5',
      },
      rotate: {
        '270': '270deg',
      },
      colors: {
        ahy: {
          bg: '#efeadd',
          blue: '#0d2f47',
          "blue-light": '#064f84',
          red: '#d83a3a',
          "red-light": '#ff4545',
          error: '#ea4436',
          'error-background': '#ffd4d0',
          green: '#14714f',
          'stock-green': '#258635',
          yellow: '#f0d102',
          "yellow-card": "#fef9eb",
          khaki: '#dfb897',
          gray: '#d1d1d1',
          'paypal-blue': '#0670bb',
          "border-color": '#002b45',
          "body-text-color": '#59555c',
          'swatch-border': '#ececeb',
          'rating-text': '#55585b',
          'rating-green': '#344a48'
        },
        primary: {
          lighter: colors.blue["300"],
          DEFAULT: colors.blue["800"],
          darker: colors.blue["900"],
        },
        secondary: {
          lighter: colors.blue["100"],
          DEFAULT: colors.blue["200"],
          darker: colors.blue["300"],
        },
        background: {
          lighter: colors.blue["100"],
          DEFAULT: colors.blue["200"],
          darker: colors.blue["300"],
        },
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet,
      },
      textColor: {
        orange: colors.orange,
        red: {
          ...colors.red,
          DEFAULT: colors.red["500"],
        },
        primary: {
          lighter: colors.gray["700"],
          DEFAULT: colors.gray["800"],
          darker: colors.gray["900"],
        },
        secondary: {
          lighter: colors.gray["400"],
          DEFAULT: colors.gray["600"],
          darker: colors.gray["800"],
        },
      },
      backgroundColor: {
        primary: {
          lighter: colors.blue["600"],
          DEFAULT: colors.blue["700"],
          darker: colors.blue["800"],
        },
        secondary: {
          lighter: colors.blue["100"],
          DEFAULT: colors.blue["200"],
          darker: colors.blue["300"],
        },
        container: {
          lighter: "#ffffff",
          DEFAULT: "#fafafa",
          darker: "#f5f5f5",
        },
      },
      borderWidth: {
        '3': '3px',
        '5': '5px',
        '6': '6px',
      },
      borderColor: {
        primary: {
          lighter: colors.blue["600"],
          DEFAULT: colors.blue["700"],
          darker: colors.blue["800"],
        },
        secondary: {
          lighter: colors.blue["100"],
          DEFAULT: colors.blue["200"],
          darker: colors.blue["300"],
        },
        container: {
          lighter: "#f5f5f5",
          DEFAULT: "#e7e7e7",
          darker: "#b6b6b6",
        },
      },
      minWidth: {
        8: spacing["8"],
        20: spacing["20"],
        40: spacing["40"],
        48: spacing["48"],
      },
      minHeight: {
        14: spacing["14"],
        "screen-25": "25vh",
        "screen-50": "50vh",
        "screen-75": "75vh",
      },
      maxHeight: {
        0: "0",
        "screen-25": "25vh",
        "screen-50": "50vh",
        "screen-75": "75vh",
      },
      container: {
        center: true,
        padding: "1.5rem",
      },
    },
  },
  plugins: [
    require("@tailwindcss/forms"),
    require("@tailwindcss/typography"),
    require('@tailwindcss/line-clamp'),
    require('@tailwindcss/aspect-ratio'),
    plugin(({ theme, addVariant, prefix, e: escape }) => {
      // https://github.com/AndyOGo/tailwindcss-nested-groups
      const groupLevel = theme("groupLevel") || 3;
      const groupScope = theme("groupScope") || "scope";
      const groupVariants = theme("groupVariants") || ["hover", "focus"];

      groupVariants.forEach(groupVariant => {
        addVariant(`group-${groupVariant}`, ({ modifySelectors, separator }) => {
          return modifySelectors(({ selector }) => {
            return selectorParser(root => {
              root.walkClasses(node => {
                // Regular group
                const value = node.value;
                // eslint-disable-next-line functional/immutable-data
                node.value = `group-${groupVariant}${separator}${value}`;

                if (node.parent && node.parent.parent) {
                  node.parent.insertBefore(node, selectorParser().astSync(prefix(`.group:${groupVariant} `)));

                  // Named groups
                  node.parent.parent.insertAfter(
                    node.parent,
                    selectorParser().astSync(
                      Array.from(Array(groupLevel))
                        .map(
                          (_x, index) =>
                            `${prefix(
                              `.group-${groupScope}:${groupVariant}${Array.from(Array(index))
                                .map(() => ` > :not(.group-${groupScope})`)
                                .join("")} > .`,
                            )}${escape(`group-${groupScope}-${groupVariant}${separator}${value}`)}`,
                        )
                        .join(","),
                    ),
                  );
                }
              });
            }).processSync(selector);
          });
        });
      });
    }),
  ],
  // Examples for excluding patterns from purge
  content: [
    // this theme's phtml and layout XML files
    "../../**/*.phtml",
    "../../*/layout/*.xml",
    // The theme-module templates are included automatically in the purge config since Hyvä 1.1.15, but
    // for themes based on earlier releases, enable the appropriate path to the theme-module below:
    // hyva theme-module templates (if this is the default theme in vendor/hyva-themes/magento2-default-theme)
    //'../../../magento2-theme-module/src/view/frontend/templates/**/*.phtml',
    // hyva theme-module templates (if this is a child theme)
    "../../../../../../../vendor/hyva-themes/magento2-theme-module/src/view/frontend/templates/**/*.phtml",
    // parent theme in Vendor (if this is a child-theme)
    "../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml",
    // app/code phtml files (if need tailwind classes from app/code modules)
    "../../../../../../../app/code/**/*.phtml",
    // react app src files (if Hyvä Checkout is installed in app/code)
    //'../../../../../../../app/code/**/src/**/*.jsx',
    // react app src files in vendor (If Hyvä Checkout is installed in vendor)
    //'../../../../../../../vendor/hyva-themes/magento2-hyva-checkout/src/reactapp/src/**/*.jsx',
    //'../../../../../../../vendor/hyva-themes/magento2-hyva-checkout/src/view/frontend/templates/react-container.phtml',
    // Hyva React checkout components
    "../../../../../../../vendor/hyva-themes/magento2-react-checkout/src/reactapp/src/**/*.jsx",
    "../../../../../../../vendor/hyva-themes/magento2-react-checkout/src/view/frontend/templates/react-container.phtml",
    // widget block classes from app/code
    //'../../../../../../../app/code/**/Block/Widget/**/*.php'
    //Page Builder  
    '../../../../../../../vendor/hyva-themes/magento2-page-builder/**/*.phtml'
  ],
});
if (require('fs').existsSync('./tailwind.browser-jit-config.js')) {

  function isObject(item) {
    return (item && typeof item === 'object' && !Array.isArray(item));
  }

  function mergeDeep(target, ...sources) {
    if (!sources.length) return target;
    const source = sources.shift();

    if (isObject(target) && isObject(source)) {
      for (const key in source) {
        if (isObject(source[key])) {
          if (!target[key]) Object.assign(target, { [key]: {} });
          mergeDeep(target[key], source[key]);
        } else {
          Object.assign(target, { [key]: source[key] });
        }
      }
    }

    return mergeDeep(target, ...sources);
  }

  mergeDeep(module.exports, require('./tailwind.browser-jit-config.js'));
}
