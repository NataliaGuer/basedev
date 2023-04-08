import { RoutingElement } from "@entities";
import Homepage from "@features/homepage/homepage.component";
import { useMemo } from "react";
import { Route } from "react-router-dom";

export default () => {

    const routingElements: RoutingElement[] = useMemo(
        () => {
            return [
                {
                    route: '*',
                    component: Homepage
                }
            ]
        }, []
    )

    return (
        <>
            {routingElements.map(
                    element => <Route path={element.route} Component={element.component}></Route> 
                )
            }
        </>
    )
}