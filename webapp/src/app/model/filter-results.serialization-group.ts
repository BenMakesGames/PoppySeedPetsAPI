export interface FilterResultsSerializationGroup<T>
{
  page: number;
  pageSize: number;
  pageCount: number;
  resultCount: number;
  results: T[];
  unfilteredTotal: number|null;
}