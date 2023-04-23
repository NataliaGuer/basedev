import * as React from "react";
import * as ReactDOM from "react-dom";
import {
    createBrowserRouter,
    RouterProvider,
} from "react-router-dom";

import HomePage, { homepageLoader } from "./feature/homepage";
import Team, { teamLoader } from "./routes/team";

const router = createBrowserRouter([
    {
        path: "/",
        element: <Homepage />,
        loader: rootLoader,
        children: [
            {
                path: "team",
                element: <Team />,
                loader: teamLoader,
            },
        ],
    },
]);

ReactDOM.createRoot(document.getElementById("root")).render(
    <RouterProvider router={router} />
);