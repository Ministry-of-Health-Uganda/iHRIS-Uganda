<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:datetime="http://exslt.org/dates-and-times"
                exclude-result-prefixes="datetime">
    <xsl:output method="xml" indent="yes"/>

    <xsl:template match="/">
        <dataValueSet>
            <xsl:attribute name="xmlns"><xsl:text>http://dhis2.org/schema/dxf/2.0</xsl:text></xsl:attribute>

            <xsl:attribute name="period">
                <xsl:for-each select="ihrisReport/reportDetails">
                    <xsl:variable name="dt" select="whenGenerated"/>
                    <xsl:if test="substring($dt, 6, 2) &lt; 4 ">
                        <xsl:value-of select="concat(
							  substring($dt, 1, 4),'Q','1'
							  )"/>
                    </xsl:if>

                    <xsl:if test="substring($dt, 6, 2) &gt;3 and substring($dt, 6, 2) &lt;7">
                        <xsl:value-of select="concat(
							  substring($dt, 1, 4),'Q','2'
							  )"/>
                    </xsl:if>

                    <xsl:if test="substring($dt, 6, 2) &gt;6 and substring($dt, 6, 2) &lt;10">
                        <xsl:value-of select="concat(
							  substring($dt, 1, 4),'Q','3'
							  )"/>
                    </xsl:if>

                    <xsl:if test="substring($dt, 6, 2) &gt;9">
                        <xsl:value-of select="concat(
							  substring($dt, 1, 4),'Q','4'
							  )"/>
                    </xsl:if>

                </xsl:for-each>
            </xsl:attribute>

            <xsl:attribute name="completeDate">
                <xsl:for-each select="ihrisReport/reportDetails">
                    <xsl:variable name="dt" select="whenGenerated"/>
                    <xsl:value-of select="concat(
						  substring($dt, 1, 4),'-',substring($dt, 6, 2),'-',substring($dt, 9, 2)
						  )"/>
                </xsl:for-each>
            </xsl:attribute>

            <xsl:for-each select="ihrisReport/reportData/dataRow">
                <dataValue>
                    <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>

                    <xsl:attribute name="dataElement">
                        <xsl:for-each select="dataElement" >
                            <xsl:if test="@name='job+id'">
                                <xsl:value-of select="." />
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:attribute>

                    <xsl:attribute name="orgUnit">
                        <xsl:for-each select="dataElement" >
                            <xsl:if test="@name='facility+id'">
                                <xsl:value-of select="." />
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:attribute>

                    <xsl:attribute name="value">
                        <xsl:for-each select="dataElement" >
                            <xsl:if test="@name='total'">
                                <xsl:value-of select="." />
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:attribute>

                </dataValue>
            </xsl:for-each>


        </dataValueSet>
    </xsl:template>
</xsl:stylesheet>