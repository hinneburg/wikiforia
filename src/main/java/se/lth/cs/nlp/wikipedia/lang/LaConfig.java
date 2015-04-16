/**
 * This file is part of Wikiforia.
 *
 * Wikiforia is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Wikiforia is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wikiforia. If not, see <http://www.gnu.org/licenses/>.
 */
 package se.lth.cs.nlp.wikipedia.lang;

//Autogenerated from Wikimedia sources at 2015-04-16T13:55:11+00:00

public class LaConfig extends TemplateConfig {
	public LaConfig() {
		addNamespaceAlias(-1, "Specialis");
		addNamespaceAlias(1, "Disputatio");
		addNamespaceAlias(2, "Usor");
		addNamespaceAlias(3, "Disputatio_Usoris");
		addNamespaceAlias(5, "Disputatio_{{GRAMMAR:genitive|Wikipedia}}");
		addNamespaceAlias(6, "Fasciculus", "Imago");
		addNamespaceAlias(7, "Disputatio_Fasciculi", "Disputatio_Imaginis");
		addNamespaceAlias(8, "MediaWiki");
		addNamespaceAlias(9, "Disputatio_MediaWiki");
		addNamespaceAlias(10, "Formula");
		addNamespaceAlias(11, "Disputatio_Formulae");
		addNamespaceAlias(12, "Auxilium");
		addNamespaceAlias(13, "Disputatio_Auxilii");
		addNamespaceAlias(14, "Categoria");
		addNamespaceAlias(15, "Disputatio_Categoriae");

	}

	@Override
	protected String getSiteName() {
		return "Wikipedia";
	}

	@Override
	protected String getWikiUrl() {
		return "http://la.wikipedia.org/";
	}

	@Override
	public String getIso639() {
		return "la";
	}
}