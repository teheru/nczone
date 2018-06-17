# nC Zone Frontend

**Development server**

The frontend is separate from the backend application and an be launched with:

`npm run serve`

*Note:* Change `baseUrl` in api.js if needed (todo: add an optional baseUrl for development that can be configured outside)


**Manual steps to use with the extension in PhpBB**

1. build frontend js

     `npm run build`
     
2. take resulting js file (`build/js`) and copy it to `../styles/prosilver/theme/` folder

3. update `../styles/prosilver/template/event/overall_header_head_append.html` to the correct file name (hash)
