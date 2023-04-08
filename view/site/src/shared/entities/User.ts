import { Address } from "./Address"

export type User = {
    name: string
    surname: string
    email: string
    address: Address
}

export type UserData = {
    data: User;
    error: Error;
}
