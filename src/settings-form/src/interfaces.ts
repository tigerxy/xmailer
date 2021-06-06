export interface Settings {
  spam: boolean;
  replyTo: boolean;
  addPageName: boolean;
  imap: Server;
  smtp: Server;
  lists: Array<List>;
  allow: Array<string>;
  footer: Footer;
  userAttribute: string;
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
export interface Footer {
    plain: string;
    html: string;
}
export interface SSLOptions {
  imap: Array<SSLOption>;
  smtp: Array<SSLOption>;
}
export interface SSLOption {
  id: string;
  description: string;
  port: number;
}
export interface Group {
    id: number;
    name: string;
}

export const PlaceholderEmail = "test@gmail.com";