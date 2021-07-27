<?php

declare(strict_types=1);


/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2021
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCP\Circles;


/**
 * Interface ICirclesManager
 *
 * @package OCP\Circles
 */
interface ICirclesManager {

	public function getFederatedUser(string $federatedId, int $type = IFederatedUser::TYPE_SINGLE): IFederatedUser;

	public function startSession(?IFederatedUser $federatedUser = null): void;

	public function startSuperSession(): void;

	public function startOccSession(
		string $userId,
		int $userType = IFederatedUser::TYPE_SINGLE,
		string $circleId = ''
	): void;

	public function stopSession(): void;

	public function getCurrentFederatedUser(): IFederatedUser;

	public function getQueryHelper(): CirclesQueryHelper;

	public function createCircle(
		string $name,
		?FederatedUser $owner = null,
		bool $personal = false,
		bool $local = false
	): Circle;

	public function destroyCircle(string $singleId): void;

	public function getCircles(?CircleProbe $probe = null): array;

	public function getCircle(string $singleId, ?CircleProbe $probe = null): Circle;

	public function addMember(string $circleId, FederatedUser $federatedUser): Member;

	public function levelMember(string $memberId, int $level): Member;

	public function removeMember(string $memberId): void;

	public function getLink(string $circleId, string $singleId): Membership;

	public function getDefinition(Circle $circle): string;

	public function getMember(string $circleId, string $singleId): Member;

	public function getMemberById(string $memberId): Member;

}
