export interface Settings {
    spam: boolean;
    replyto: boolean;
    addpagename: boolean;
    imap: Server;
    smtp: Server;
    lists: Array<List>;
    allow: Array<string>;
}
export interface Server {
    host: string;
    user: string;
    password: string;
    ssl: string;
    port: number;
}
export interface List {
    name: string;
    email: string;
    mailbox: string;
    grpId: number;
}
export interface SSLOptions {
    imap: Array<SSLOption>,
    smtp: Array<SSLOption>
}
export interface SSLOption {
    id: string,
    description: string,
    port: number
}