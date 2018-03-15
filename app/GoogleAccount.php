<?php

namespace App;

use Google_Client;
use Google_Service_Exception;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Illuminate\Database\Eloquent\Model;

class GoogleAccount extends Model
{
    protected $fillable = ['email', 'access_token', 'refresh_token'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gmail(): Google_Service_Gmail
    {
        $client = $this->google();

        $client->setAccessToken($this['access_token']);

        return new Google_Service_Gmail($client);
    }

    protected function google(): Google_Client
    {
        return app(Google_Client::class);
    }

    public function amazonConfirmOrderMails()
    {
        $query = "from:auto-confirm@amazon.com after:2018-03-11 \"Your Amazon.com order of\"";

        $mails = $this->getMails($query);

        collect($mails)->map(function (Google_Service_Gmail_Message $mail) {
            $content = $this->mail($mail->getId());

            preg_match('/You ordered   \"(.+)...\"/imsU', $content, $matches);

            return [];
        });

        return;
    }

    public function mail($id): string
    {
        try {
            $mail = $this->mailDetails($id);

            return $this->parseMailTextContent($mail);
        } catch (Google_Service_Exception $exception) {
            $this->refreshAccessToken();

            // Retry with new access_token
            return $this->mail($id);
        }
    }

    protected function parseMailTextContent(Google_Service_Gmail_Message $message): string
    {
        $payload = $message->getPayload();

        return collect($payload->getParts())
            ->filter(function (Google_Service_Gmail_MessagePart $part) {
                return $part->getMimeType() === 'text/plain';
            })->map(function (Google_Service_Gmail_MessagePart $part) {
                return base64_decode($part->getBody()->getData());
            })->first();
    }

    protected function mailDetails($id): Google_Service_Gmail_Message
    {
        return $this->gmail()->users_messages->get('me', $id);
    }

    protected function refreshAccessToken(): bool
    {
        $response = $this->gmail()->getClient()->refreshToken($this['refresh_token']);

        return $this->update(
            array_only($response, ['access_token', 'refresh_token'])
        );
    }

    protected function getMails(string $q = null): array
    {
        return $this->gmail()
            ->users_messages
            ->listUsersMessages('me', compact('q'))
            ->getMessages();
    }
}
