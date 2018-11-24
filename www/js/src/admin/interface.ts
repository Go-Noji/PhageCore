import {AxiosError, CancelTokenSource} from "axios";

//AdminModuleのためのInterface
export interface AdminState{
  lastApi: string,
  data: {[key: string]: string},
  error: AxiosError,
  source: CancelTokenSource|null
}

//EditModuleのためのInterface
export interface EditData {
  api: string,
  value: string,
  connect: boolean,
  success: boolean
}
export interface EditState {
  id: number,
  data: {
    [key: string]: EditData
  }
}
