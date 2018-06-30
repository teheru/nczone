# nC Zone Frontend

**Development server**  

The frontend is separate from the backend application and an be launched with:

`npm run serve`

**.env**  

Environment variables are defined in `.env` and should be overwritten for local 
development in a file called `.env.development.local`.
Variables starting with `VUE_APP_` are inlined into the resulting js lib.

Possible variables: 
```
// Base path of the api
VUE_APP_API_BASE

// Style name (corresponds to folder names in src/style)
// This is only used in development mode to switch between styles.
// For production build all stylesheets are built (see vue.config.js)
VUE_APP_STYLE_NAME 
```

**Manual steps to use with the extension in PhpBB**

1. build frontend js

     `npm run build`
     
2. take resulting files and copy them in the according styles folder
```
     // copy js lib to all frontend style folders
     // other generated js files can be ignored!
     cp dist/js/zone.HASH.js ../styles/STYLE_NAME/theme/zone.HASH.js
     
     // copy stylesheets to style folders
     cp dist/css/STYLE_NAME/zone.HASH.css ../styles/STYLE_NAME/theme/zone.HASH.css
     
```

3. update `../styles/STYLE_NAME/template/event/overall_header_head_append.html` to the correct file names (`HASH` changes)
