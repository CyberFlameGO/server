<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace OC\Security\VerificationToken;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use OCP\Security\VerificationToken\InvalidTokenException;
use OCP\Security\VerificationToken\IVerificationToken;

class VerificationToken implements IVerificationToken {

	/** @var IL10N */
	private $l10n;
	/** @var IConfig */
	private $config;
	/** @var ICrypto */
	private $crypto;
	/** @var ITimeFactory */
	private $timeFactory;
	/** @var ISecureRandom */
	private $secureRandom;

	public function __construct(
		IL10N $l10n,
		IConfig $config,
		ICrypto $crypto,
		ITimeFactory $timeFactory,
		ISecureRandom $secureRandom
	) {
		$this->l10n = $l10n;
		$this->config = $config;
		$this->crypto = $crypto;
		$this->timeFactory = $timeFactory;
		$this->secureRandom = $secureRandom;
	}

	/**
	 * @throws InvalidTokenException
	 */
	protected function throwInvalidTokenException(): void {
		throw new InvalidTokenException($this->l10n->t('Could not reset password because the token is invalid'));
	}

	public function check(string $token, ?IUser $user, string $subject, string $passwordPrefix = ''): void {
		if ($user === null || !$user->isEnabled()) {
			$this->throwInvalidTokenException();
		}

		$encryptedToken = $this->config->getUserValue($user->getUID(), 'core', $subject, null);
		if ($encryptedToken === null) {
			$this->throwInvalidTokenException();
		}

		try {
			$decryptedToken = $this->crypto->decrypt($encryptedToken, $passwordPrefix.$this->config->getSystemValue('secret'));
		} catch (\Exception $e) {
			$this->throwInvalidTokenException();
		}

		$splitToken = explode(':', $decryptedToken ?? '');
		if (count($splitToken) !== 2) {
			$this->throwInvalidTokenException();
		}

		if ($splitToken[0] < ($this->timeFactory->getTime() - 60 * 60 * 24 * 7) ||
			$user->getLastLogin() > $splitToken[0]) {
			$this->throwInvalidTokenException();
		}

		if (!hash_equals($splitToken[1], $token)) {
			$this->throwInvalidTokenException();
		}
	}

	public function create(IUser $user, string $subject, string $passwordPrefix = ''): string {
		$token = $this->secureRandom->generate(
			21,
			ISecureRandom::CHAR_DIGITS.
			ISecureRandom::CHAR_LOWER.
			ISecureRandom::CHAR_UPPER
		);
		$tokenValue = $this->timeFactory->getTime() .':'. $token;
		$encryptedValue = $this->crypto->encrypt($tokenValue, $passwordPrefix . $this->config->getSystemValue('secret'));
		$this->config->setUserValue($user->getUID(), 'core', $subject, $encryptedValue);

		return $token;
	}
}
