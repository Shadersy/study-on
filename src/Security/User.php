<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface
{
    /**
     * @Assert\Email(
     *     message = "'{{ value }}' не является email-адресом.",
     * )
     */
    private $email;

    private $apiToken;

    /**
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Пароль должен быть как минимум из {{ limit }} символов",
     *      maxMessage = "Ваш пароль не должен быть длиннее {{ limit }} символов"
     * )
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message = "Пароли не совпадают")
     *
     */
    private $conformationPassword;

    private $roles = [];

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setConformationPassword(string $conformationPassword)
    {
        $this->conformationPassword = $conformationPassword;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getConformationPassword(): ?string
    {
        return $this->conformationPassword;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getBalance()
    {
        // TODO: Implement getBalance() method.
    }
}
