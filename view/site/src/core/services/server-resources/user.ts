import { FetchParameters, UserData } from "../../../shared/entities";
import { Fetch } from "./fetch";
import { REST_BASE_URL } from "../../../shared/globals";

export class User extends Fetch {

    constructor(path: string) {
        const parameters: FetchParameters = {
            url: `${REST_BASE_URL}/${path}`,
            options: {
                mode: 'same-origin',
                method: 'GET'
            },
            
        }
        super(parameters);
    }

    public async getData(): Promise<UserData> {
        return await this._fetchData()
    }
}