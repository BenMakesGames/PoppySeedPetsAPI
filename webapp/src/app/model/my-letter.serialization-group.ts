export interface MyLetterSerializationGroup
{
  id: number;
  letter: { sender: string, title: string, body: string, attachment: { name: string, nameWithArticle: string }|null };
  receivedOn: string;
  comment: string;
  isRead: boolean;
}
